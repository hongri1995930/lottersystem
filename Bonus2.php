<?php
class SaeMysql {
	//连接服务器
	function __construct(){
		$conn=mysql_connect("127.0.0.1","*****","*****");
        	mysql_select_db("lotter",$conn);
		mysql_query("SET NAMES 'utf8'");
	}
	
	function runSql($sql){
		return mysql_query($sql);
	}

	function closeDb(){
		$conn=mysql_connect("127.0.0.1","hongri","apple");
		mysql_close($conn);
	}
}

function Bonus($postObj, $url){
    $fromusername = $postObj->FromUserName;
    $mysql = new SaeMysql();    
    $sql = "SELECT  * FROM  `Bonus` WHERE UserName =  '{$fromusername}'";
//    $mysql->runSql($sql);
    $data = $mysql->runSql( $sql );
    $line = mysql_num_rows($data);
    if ($line > 0)
    {
	$res=mysql_fetch_array($data);
        if($res['Section1'] == 1 && $res['Section2'] == 1)//已抽取过
        {
            $result = "每个人只有一次抽奖机会哦~";
        }
        else//未抽取过
        {
        	//完成Section1
            $sql = "Update `Bonus` set `Section1` = '1', `PicUrl` = '{$url}' where `UserName` = '{$fromusername}'";
            $mysql->runSql($sql);
            if($res['Section2'] == 1){
                $num = rand(1,9).rand(1,9).rand(1,9).rand(1,9).rand(1,9);
				$sql = "Update `Bonus` set `UserCode` = '{$num}' where `UserName` = '{$fromusername}'";
				$mysql->runSql($sql);
				$result = "任务完成！\n你的国际码为<".$num.">\n中奖号码会公布在大本营，请于活动当天到大本营领取奖品~";
        	}else{
            		$result = "任务已完成50%，回复<投票#国家>支持喜欢的国家摊位，完成任务获取国际码，抽取大奖~";
        	}
        }
    }else{
        $sql = "Insert into `Bonus`(`UserName`, `Section1`, `PicUrl`) values('{$fromusername}', '1', '{$url}')";
    	$mysql->runSql($sql);
        $result = "任务已完成50%，回复<投票#国家>支持喜欢的国家摊位，完成任务获取国际码，抽取大奖~";
    }    
	/*if ($mysql->errno() != 0)
	{
		die("Error:" . $mysql->errmsg());
  	}*/
	$mysql->closeDb();
    return $result;
}

function vote($postObj, $keyword){
    $fromusername = $postObj->FromUserName;
    $mysql = new SaeMysql();   
    $sql = "SELECT * FROM  `Bonus` WHERE `UserName` = '{$fromusername}'";
    //$mysql->runSql($sql);
    $data = $mysql->runSql($sql);
    $line = mysql_num_rows($data);
    if ($line > 0)
    {
	$res=mysql_fetch_array($data);
        if($res['Section1'] == 1 && $res['Section2'] == 1)//已抽取过
        {
            $result = "每个人只有一次抽奖机会哦~";
        }
        else//未抽取过
        {
        	//完成Section2
            $sql = "Update `Bonus` set `Section2` = '1', `UserVote` = '{$keyword}' where `UserName` = '{$fromusername}'";
    		$mysql->runSql($sql);
            if($res['Section1'] == 1)
            {
            	$num = rand(1,9).rand(1,9).rand(1,9).rand(1,9).rand(1,9);
		$sql = "Update `Bonus` set `UserCode` = '{$num}' where `UserName` = '{$fromusername}'";
		$mysql->runSql($sql);
		$result = "任务完成！\n<你的国际码为>".$num."\n中奖号码会公布在大本营，请于活动当天到大本营领取奖品~";
            }else{
            	$result = "任务已完成50%，分享你参与活动的露脸照片到朋友圈并截屏发送到本平台，完成任务获取国际码，抽取大奖~";
	    }
        }
    }else{
        $sql = "Insert into `Bonus`(`UserName`, `Section2`, `UserVote`) values('{$fromusername}', '1', '{$keyword}')";
    	$mysql->runSql($sql);
        $result = "任务已完成50%，分享你参与活动的露脸照片到朋友圈并截屏发送到本平台，完成任务获取国际码，抽取大奖~";
    }
	/*if ($mysql->errno() != 0)
	{
		die("Error:" . $mysql->errmsg());
    }*/
	$mysql->closeDb();
    return $result;
}

//将图片存到云端，此功能暂时被砍
function Shorten($url){
    $ch=curl_init();
	curl_setopt($ch,CURLOPT_URL,"http://dwz.cn/create.php");
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$data=array('url'=>$url);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
	$strRes=curl_exec($ch);
	curl_close($ch);
	$arrResponse=json_decode($strRes,true);
	return $arrResponse['tinyurl'];
}

function GetBonus($object, $UserPrize, $flag=0){
    $mysql = new SaeMysql();
    $sql = "SELECT  * FROM  `Bonus` where UserPrize = '{$UserPrize}'";
    $data = $mysql->runSql($sql);
    //$data = $mysql->getData($sql);
    $line = mysql_num_rows($data);
    if($UserPrize == '一等奖' && $line == 1)
    {
        $content = '一等奖名额只有1个，且已抽完。';
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
    	$resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
    }
    elseif($UserPrize =='二等奖' && $line == 6)
    {
        $content = '二等奖名额只有4个，且已抽完。';
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
    	$resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
    }
    else
    {
        $sql = "SELECT  * FROM  `Bonus` where UserPrize = '' and UserCode <> '' ";
        $data = $mysql->runSql($sql); 
    	//$data = $mysql->getData($sql);
    	$line1 = mysql_num_rows($data);
        if($line1 == 0)
        {
            $content = '人数不达要求，请稍后抽取。查看参与人数请回复“结果”。';
        	$textTpl = "<xml>
                    	<ToUserName><![CDATA[%s]]></ToUserName>
                    	<FromUserName><![CDATA[%s]]></FromUserName>
                    	<CreateTime>%s</CreateTime>
                    	<MsgType><![CDATA[text]]></MsgType>
                    	<Content><![CDATA[%s]]></Content>
                    	<FuncFlag>0</FuncFlag>
                    	</xml>";
    		$resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        }
        else
        {
        	$sql = "SELECT  * FROM  `Bonus`";
        	$data = $mysql->runSql($sql);
            	//$data = $mysql->getData($sql);
    		$line = mysql_num_rows($data);
    		do {
    			$id = rand(1,$line);
    			$sql = "SELECT  * FROM  `Bonus` where Id = '{$id}'";
    			$data = $mysql->runSql($sql);
    			//$data = $mysql->getData($sql);
			$res=mysql_fetch_array($data);
    		} while ($res['UserPrize'] != null or $res['UserCode'] == null);
    		$content = "恭喜你获得<{$UserPrize}>，请及时到兑奖处兑换奖品~\n兑换时请出示国际码，活动结束前未及时兑换视为弃权。";
    		$textTpl = "<xml>
                    	<ToUserName><![CDATA[%s]]></ToUserName>
                    	<FromUserName><![CDATA[%s]]></FromUserName>
                    	<CreateTime>%s</CreateTime>
                    	<MsgType><![CDATA[text]]></MsgType>
                    	<Content><![CDATA[%s]]></Content>
                    	<FuncFlag>0</FuncFlag>
                    	</xml>";
    		$resultStr = sprintf($textTpl, $res['UserName'], $object->ToUserName, time(), $content, $flag);
    		$sql = "Update `Bonus` set `UserPrize` = '{$UserPrize}' where `Id` = '{$id}'";
    		$mysql->runSql($sql);
        }
    }
    $mysql->closeDb();
    return $resultStr;
}

function GetCount(){
    $mysql = new SaeMysql();
    $sql = "SELECT  * FROM  `Bonus`";
    $data = $mysql->runSql($sql);
    //$data = $mysql->getData($sql);
    $count = mysql_num_rows($data);
    $result = "目前参与人数：<".$count.">人\n";
    $sql = "SELECT  * FROM  `Bonus` where UserPrize = '一等奖'";
    $data = $mysql->runSql($sql);
    //$data = $mysql->getData($sql);
    $res=mysql_fetch_array($data);
    $result = $result."一等奖：<".$res['UserCode'].">\n二等奖：";
    $sql = "SELECT  * FROM  `Bonus` where UserPrize = '二等奖'";
    $data = $mysql->runSql($sql);
    //$data = $mysql->getData($sql);
    $count = mysql_num_rows($data);
    for($i = 0; $i < $count; $i ++){
        $result = $result.$res[$i]['UserCode']."、";
    }
    $sql = "SELECT  * FROM  `Bonus` where UserPrize = '三等奖'";
    $data = $mysql->runSql($sql);
    //$data = $mysql->getData($sql);
    $line = mysql_num_rows($data);
    $res=mysql_fetch_array($data);
    $result = $result."\n三等奖：";
    for($j = 0; $j < $line; $j ++)
    {
        $result = $result.$res[$j]['UserCode']."、";
    }
    return $result;
    $mysql->closeDb();
}
?>
