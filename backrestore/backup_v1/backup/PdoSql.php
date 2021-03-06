<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 18-4-25
 * Time: 上午8:52
 * pdo 备份数据库 sql 格式
 */
namespace backup;
use PDO;

include "Backup.php";

class PdoSql extends Backup{

    protected  $pdo; // pdo 类对象

    //连接数据库
    protected  function conn()
    {
        try{
            $dsn='mysql:host='.$this->host.';port='.$this->prot.';dbname='.$this->db;
            $username=$this->dbuser;
            $passwd=$this->dbpw;
            $this->pdo=new PDO($dsn,$username,$passwd);
			$this->pdo->query("set names ".$this->charset);

        }catch (\Exception $e)
        {
            die("Error: " . $e->getMessage());
        }
    }
	
	// 取得mysql 版本号
	protected function mysql_v()
	{
		 $res=$this->pdo->query("select VERSION()");
         $version=$res->fetch(PDO::FETCH_NUM);
         $sql_head="-- MySql版本-pdo：".$version[0].PHP_EOL;
		 $res=null;
		 return $sql_head;
	}

    // 取得所有表名 并且判断数据表是否存在
    protected function table_name()
    {
        // 取得数据库中所有的表
        $sql="show tables";
        $table_result=$this->pdo->query($sql);
        $table_name=$table_result->fetchAll(PDO::FETCH_COLUMN);
        $table_result=null;
        if(empty($table_name))
        {
            exit("此数据库下没有表");
        }
        //判断用户传入的数据表数据库中是否存在
        if(!empty($this->table))
        {
            $table_name=$this->table_is($table_name);
        }
        return $table_name;
    }

    // 取得表结构数据(语句)---- 没有使用字符转义
    protected function table_structure($table_name)
    {
        $table_sql="show create table `".$table_name."`";
        $result=$this->pdo->query($table_sql);
        $row=$result->fetch(PDO::FETCH_NUM);
        return $row[1];
    }

    // 查询表数据----根据表名查询表数据
    protected function select_insert($table_name)
    {
        $table_data=array();
        $sql="select * from `".$table_name."`";
        $resource=$this->pdo->query($sql);
        $table_data=$resource->fetchAll(PDO::FETCH_ASSOC);
        return $table_data;
    }

    // 取得字段类型
    protected function field_type($table_name)
    {
        $field_type="show full fields from `".$table_name.'`'; // 取得字段类型
        $field_type_r=$this->pdo->query($field_type);
        while($field_type=$field_type_r->fetch(PDO::FETCH_ASSOC))
        {
            $table_field_type[]=$field_type["Type"];
        }
        return $table_field_type;
    }

    // 锁表
    protected function lock_table($table_name)
    {
        // 锁表--- 写入受限制，读不限制
        $lock="lock table ".$table_name." read";
        $lock_re=$this->pdo->query($lock);
        return $lock_re;
    }

    //解表
    protected function unlock_table($lock_re)
    {
        $unlock="unlock tables";
        $unlock_re=$this->pdo->query($unlock);
        // 释放锁表 解表
        $unlock_re=$lock_re=null;
    }

    // 释放资源
    public function __destruct()
    {
        $this->pdo=null;
    }
}
// 调用测试
/*$table_name=["dede_archives"];
$pdo=new PdoSql('127.0.0.1','back','root','admin');
$pdo->sql_insert();*/