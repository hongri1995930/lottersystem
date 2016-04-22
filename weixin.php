<?php
/**
  * wechat php 
  */

//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();//实例化
$wechatObj->responseMsg();//正式使用时返回消息
//$wechatObj->valid();//测试连接

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr))
        {
		//对微信服务器返回的消息解析
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $RX_TYPE = trim($postObj->MsgType);
                switch($RX_TYPE)
                {
                    case "text":
                        $resultStr = $this->handleText($postObj);
                        break;
                    case "event":
                        $resultStr = $this->handleEvent($postObj);
                        break;
                    case "image":
                    	$resultStr = $this->handleImage($postObj);
                    	break;
                    default:
                        $resultStr = "Unknow msg type: ".$RX_TYPE;
                        break;
                }
                echo $resultStr;
        }else {
            echo "error";
            exit;
        }
    }

    public function handleText($postObj)
    {
	//get post data, May be due to the different environments
	$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
 	//extract post data
	if (!empty($postStr))
        {
            $keyword = trim($postObj->Content);
                if(!empty($keyword))
                {
                	$str_len = mb_strlen($keyword,'utf8');
					$str = mb_substr($keyword,0,3,"UTF-8");
                 	$str_key = mb_substr($keyword,3,$str_len -3,"UTF-8");
					if($str == "投票#" && $str_key <> ""){
	                    require('Bonus2.php');
	                    $contentStr = Vote($postObj, $str_key);                        
	                    $resultStr = $this->responseText($postObj, $contentStr);                    
                    }elseif($str == "投票"){                        
		                $resultStr = $this->responseText($postObj, "投票请注意投票格式\n“投票#国家”");
                    }elseif($str == "抽取#"){
		                require('Bonus2.php');                        
	                    $resultStr = GetBonus($postObj,$str_key);
                    }elseif($keyword == "结果"){
	                    require('Bonus2.php');
		                $contentStr = GetCount();
		                $resultStr = $this->responseText($postObj, $contentStr);
                    }else{
		                $contentStr = "参与<国际文化节>微信平台互动，赢取精美礼品！\n只要把你参与活动的露脸照片分享到朋友圈，截屏，发到本平台，并给自己喜欢的国家摊位投上一票（回复“投票#国家”即可），就能抽取大奖~\n大奖是iPad Air哦~";
	                    $resultStr = $this->responseText($postObj, $contentStr);
                    }
		            	echo $resultStr;
                } else {
	               	echo "Input something...";
                }
        }else {
        	echo "error";
        	exit;
        }
    }
    
    public function handleImage($postObj)
    {
		//取消了存储图片到服务器端
        $picUrl = $postObj->PicUrl;
        require('Bonus2.php');
        //$url = Shorten($picUrl);
	$url = NULL; 
        $contentStr = Bonus($postObj, $url);
        $resultStr = $this->responseText($postObj, $contentStr);
        return $resultStr;
    }
    
    public function handleEvent($object)
    {
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "感谢你的关注!";
                break;
            default:
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }    
    
    public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }
    
    public function responseNews($object,$title,$picurl,$url,$contentStr)
    {
    	$picTpl = "<xml>
				   <ToUserName><![CDATA[%s]]></ToUserName>
				   <FromUserName><![CDATA[%s]]></FromUserName>
				   <CreateTime>%s</CreateTime>
				   <MsgType><![CDATA[news]]></MsgType>
				   <ArticleCount>1</ArticleCount>
				   <Articles>
				   <item>
				   <Title><![CDATA[%s]]></Title>
				   <Description><![CDATA[%s]]></Description>
				   <PicUrl><![CDATA[%s]]></PicUrl>
				   <Url><![CDATA[%s]]></Url>
				   </item>
				   </Articles>
				   <FuncFlag>1</FuncFlag>
				   </xml> ";
        $resultStr = sprintf($picTpl, $object->FromUserName, $object->ToUserName, time(), $title, $contentStr, $picurl,$url);
        return $resultStr;
    }

	//用于验证签名
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );	
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}
?>
