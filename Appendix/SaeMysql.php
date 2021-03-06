<?php
/**
 * SAE Mysql服务
 * 
 * 支持主从分离
 *
 * @author Easychen <easychen@gmail.com>
 * @version $Id$
 * @package sae
 *
 */

/**
 * Sae Mysql Class
 *
 * <code>
 * $mysql = new SaeMysql();
 *
 * $sql = "SELECT * FROM `user` LIMIT 10";
 * $data = $mysql->getData( $sql );
 * $name = strip_tags( $_REQUEST['name'] );
 * $age = intval( $_REQUEST['age'] );
 * $sql = "INSERT  INTO `user` ( `name` , `age` , `regtime` ) VALUES ( '"  . $mysql->escape( $name ) . "' , '" . intval( $age ) . "' , NOW() ) ";
 * $mysql->runSql( $sql );
 * if( $mysql->errno() != 0 )
 * {
 *	 die( "Error:" . $mysql->errmsg() );
 * }
 * 
 * $mysql->closeDb();
 * </code>
 *
 * @package sae
 * @author EasyChen
 * 
 */ 
class SaeMysql 
{

	/**
	 * 构造函数 
	 *	
	 * @param bool $do_replication 是否支持主从分离，true:支持，false:不支持，默认为true 
	 * @return void
	 * @author EasyChen
	 */
	function __construct( $do_replication = true )
	{
		$conn=mysql_connect("127.0.0.1","hongri","apple");
                mysql_query("set names 'UTF_8'");
                mysql_select_db("lotter",$conn);
	}

	/**
	 * 设置keys 
	 *	
	 * 当需要连接其他APP的数据库时使用
	 * 
	 * @param string $akey AccessKey 
	 * @param string $skey SecretKey
	 * @return void
	 * @author EasyChen
	 */
	public function setAuth( $akey , $skey )
	{
		$this->accesskey = $akey;
		$this->secretkey = $skey;
	}

	/**
	 * 设置Mysql服务器端口
	 *
	 * 当需要连接其他APP的数据库时使用
	 * 
	 * @param string $port 
	 * @return void
	 * @author EasyChen
	 */
	public function setPort( $port )
	{
		$this->port = $port;
		$this->host = $this->port . '.mysql.sae.sina.com.cn';

	} 

	/**
	 * 设置Appname
	 *
	 * 当需要连接其他APP的数据库时使用
	 *
	 * @param string $appname 
	 * @return void
	 * @author EasyChen
	 */
	public function setAppname( $appname )
	{
		$this->appname =  'app_' . $appname;
	}


	/**
	 * 设置当前连接的字符集 , 必须在发起连接之前进行设置
	 *
	 * @param string $charset 字符集,如GBK,GB2312,UTF8
	 * @return void
	 */
	public function setCharset( $charset )
	{
		return $this->set_charset( $charset );
	}

	/**
	 * 同setCharset,向前兼容
	 *
	 * @param string $charset 
	 * @return void
	 * @ignore
	 */
	public function set_charset( $charset )
	{
		$this->charset = $charset;
	}

	/**
	 * 运行Sql语句,不返回结果集
	 *
	 * @param string $sql 
	 * @return void
	 */
	public function runSql( $sql )
	{
		return $this->run_sql( $sql );
	}

	/**
	 * 同runSql,向前兼容
	 *
	 * @param string $sql 
	 * @return bool
	 * @author EasyChen
	 * @ignore
	 */
	public function run_sql( $sql )
	{
		$this->last_sql = $sql;
		$ret = mysql_query( $sql , $this->db_write() );
		$this->save_error();
		return $ret;
	}

	/**
	 * 运行Sql,以多维数组方式返回结果集
	 *
	 * @param string $sql 
	 * @return array
	 * @author EasyChen
	 */
	public function getData( $sql )
	{
		return $this->get_data( $sql );
	}

	/**
	 * 同getData,向前兼容
	 *
	 * @ignore
	 */
	public function get_data( $sql )
	{
		$this->last_sql = $sql;
		$data = Array();
		$i = 0;
		$result = mysql_query( $sql , $this->do_replication ? $this->db_read() : $this->db_write()  );

		$this->save_error();

		while( $Array = mysql_fetch_array($result, MYSQL_ASSOC ) )
		{
			$data[$i++] = $Array;
		}

		mysql_free_result($result); 

		if( count( $data ) > 0 )
			return $data;
		else
			return false;	
	}

	/**
	 * 运行Sql,以数组方式返回结果集第一条记录
	 *
	 * @param string $sql 
	 * @return array
	 * @author EasyChen
	 */
	public function getLine( $sql )
	{
		return $this->get_line( $sql );
	}

	/**
	 * 同getLine,向前兼容
	 *
	 * @param string $sql 
	 * @return array
	 * @author EasyChen
	 * @ignore
	 */
	public function get_line( $sql )
	{
		$data = $this->get_data( $sql );
		return @reset($data);
	}

	/**
	 * 运行Sql,以数组方式返回结果集第一条记录的第一个字段值
	 *
	 * @param string $sql 
	 * @return mixxed
	 * @author EasyChen
	 */
	public function getVar( $sql )
	{
		return $this->get_var( $sql ); 
	} 

	/**
	 * 同getVar,向前兼容
	 *
	 * @param string $sql 
	 * @return array
	 * @author EasyChen
	 * @ignore
	 */
	public function get_var( $sql )
	{
		$data = $this->get_line( $sql );
		return $data[ @reset(@array_keys( $data )) ];
	}

	/**
	 * 同mysql_last_id函数
	 * PHP's mysql_last_id()在id为big int时,会出现溢出,用Sql查询替代掉
	 *
	 * @return int
	 * @author EasyChen
	 */
	public function lastId()
	{
		return $this->last_id();
	}

	/**
	 * 同lastId,向前兼容
	 *
	 * @return int
	 * @author EasyChen
	 * @ignore
	 */
	public function last_id()
	{
		$result = mysql_query( "SELECT LAST_INSERT_ID()" , $this->db_write() );
		return reset( mysql_fetch_array( $result, MYSQL_ASSOC ) );
	}

	/**
	 * 关闭数据库连接
	 *
	 * @return bool
	 * @author EasyChen
	 */
	public function closeDb()
	{
		return $this->close_db();
	}

	/**
	 * 同closeDb,向前兼容
	 *
	 * @return bool
	 * @author EasyChen
	 * @ignore
	 */
	public function close_db()
	{
		if( isset( $this->db_read ) )
			@mysql_close( $this->db_read );

		if( isset( $this->db_write ) )
			@mysql_close( $this->db_write );

	}

	/**
	 *  同mysql_real_escape_string
	 *
	 * @param string $str 
	 * @return string
	 * @author EasyChen
	 */
	public function escape( $str )
	{
		if( isset($this->db_read)) $db = $this->db_read ;
		elseif( isset($this->db_write) )	$db = $this->write;
		else $db = $this->db_read();

		return mysql_real_escape_string( $str , $db );
	}

	/**
	 * 返回错误码
	 * 
	 *
	 * @return int
	 * @author EasyChen
	 */
	public function errno()
	{
		return	 $this->errno;
	}

	/**
	 * 返回错误信息
	 *
	 * @return string
	 * @author EasyChen
	 */
	public function error()
	{
		return $this->error;
	}

	/**
	 * 返回错误信息,error的别名
	 *
	 * @return string
	 * @author EasyChen
	 */
	public function errmsg()
	{
		return $this->error();
	}

	/**
	 * @ignore
	 */
	private function connect( $is_master = true )
	{
		if( $is_master ) $host = 'm' . $this->host;
		else $host = 's' . $this->host;

		if( !$db = mysql_connect( $host . ':' . $this->port , $this->accesskey , $this->secretkey ) )
		{
			die('can\'t connect to mysql ' . $this->host . ':' . $this->port );
		}

		mysql_query( "SET NAMES " . $this->charset , $db );

		mysql_select_db( $this->appname , $db );

		return $db;
	}

	/**
	 * @ignore
	 */
	private function db_read()
	{
		if( isset( $this->db_read ) )
		{
			mysql_ping( $this->db_read );
			return $this->db_read;
		}
		else
		{
			if( !$this->do_replication ) return $this->db_write();
			else
			{
				$this->db_read = $this->connect( false );
				return $this->db_read;
			}
		}
	}

	/**
	 * @ignore
	 */
	private function db_write()
	{
		if( isset( $this->db_write ) )
		{
			mysql_ping( $this->db_write );
			return $this->db_write;
		}
		else
		{
			$this->db_write = $this->connect( true );
			return $this->db_write;
		}
	}

	/**
	 * @ignore
	 */
	private function save_error()
	{
		$this->error = mysql_error();
		$this->errno = mysql_errno();
	}

	private $error;
	private $errno;
	private $last_sql;


}
