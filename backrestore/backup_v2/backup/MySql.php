<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 18-4-25
 * Time: 上午8:56
 * mysql 备份sql 数据格式
 */
namespace backup;

include "Backup.php";

class MySql  extends Backup{

    // 连接数据库
    public function conn()
    {
        $this->conn = @mysql_connect ( $this->host, $this->dbuser, $this->dbpw ) or die(mysql_error());
        // 选择使用哪个数据库
        mysql_select_db ( $this->db, $this->conn) or die(mysql_error());
        // 数据库编码方式
        mysql_query ( 'SET NAMES ' . $this->charset, $this->conn );
    }
	
	// 取得mysql 版本号
	public function mysql_v()
	{
	   $sql_head="-- MySql版本-mysql：".mysql_get_server_info().PHP_EOL;
	   return $sql_head;
	}

    // 取得所有表名
    public function table_name()
    {
        $table_name=array();
        // 如果备份整个数据库
        $table_sql="SHOW TABLES";
        $table_result=mysql_query($table_sql);
        $table_c=mysql_num_rows($table_result);

        if($table_c < 1)
        {
            exit("此数据库下没有表");
        }
        for($i=0; $i<$table_c; $i++)
        {
           array_push($table_name,mysql_tablename($table_result,$i));
        }
        // 释放资源集
        mysql_free_result($table_result);

        //判断用户传入的数据表数据库中是否存在
        if(!empty($this->table))
        {
            $table_name=$this->table_is($table_name);
        }
        return $table_name;
    }

    // 取得表结构数据(语句)
    public function table_structure($table_name)
    {
        $table_sql="show create table `".$table_name."`";
        $result=mysql_query($table_sql);
        $row=mysql_fetch_array($result);
        mysql_free_result($result);
        return $row[1];
    }

    // 查询表数据----根据表名查询表数据
    public function select_insert($table_name)
    {
        $table_data=array();
        $sql="select * from `".$table_name."`";
        $resource=mysql_query($sql);
        while($data=mysql_fetch_assoc($resource))
        {
            $table_data[]=$data;
        }
       return  $table_data;
    }

    // 取得字段类型
    public function field_type($table_name)
    {
        $field_type="show full fields from `".$table_name.'`'; // 取得字段类型
        $field_type_r=mysql_query($field_type);
        while($field_type=mysql_fetch_assoc($field_type_r))
        {
            $table_field_type[]=$field_type["Type"];
        }
        mysql_free_result($field_type_r);
        return $table_field_type;
    }

    // 锁表
    public function lock_table($table_name)
    {
        $lock="lock table ".$table_name." read";
        $lock_re=mysql_query($lock);
    }

    //解表
    public function unlock_table($lock_re)
    {
        $unlock="unlock tables";
        $unlock_re=mysql_query($unlock);
        // 释放锁表 解表
        $unlock_re=$lock_re=null;
    }

    // 释放资源
    public function __destruct()
    {
       mysql_close($this->conn);
    }
}


