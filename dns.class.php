<?php 
    class dns{
        private $id = 0x0000;
        private $qr = 0;
        private $opcode = 0x0;
        private $aa = 0;
        private $tc = 0;
        private $rd = 0;
        private $ra = 0;
        private $z = 0x0;
        private $rcode = 0x0;
        private $qdcount = 0x0000;
        private $ancount = 0x0000;
        private $nscount = 0x0000;
        private $arcount = 0x0000;
        
        private $qname = '';
        private $code_qname = 0x0000;
        private $qtype = 00;
        private $qclass = 00;
        
        private $name = 0x0000;
        private $type = 0x0000;
        private $class = 0x0000;
        private $ttl = 0x00000000;
        private $rdlength = 0x0000;
        private $rdata = 0x00000000;
        
        private $socket = '';
        private $query_length = 0;
        
        private $offset = 0;
        
        public function __construct(){
            if($this->socket)
                socket_close($this->socket);
            $this->id = $this->getId();
            $this->rd = 1;
            $this->qdcount = 0x0001;            
            $this->socket = socket_create(AF_INET,SOCK_DGRAM,SOL_UDP);
            if($this->socket == false){
                echo "socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
                //exit;
            }
        }
        public function send($ip,$port,$domain){
            $this->offset = 0;
            $encode = $this->encodeDomain($domain);
            //echo bin2hex($encode['msg']);//发送的报文16进制内容    
            socket_sendto($this->socket, $encode['msg'],$encode['len'], 0,$ip,$port);            
        }
        public function recv(){
            if (!socket_set_option($this->socket,SOL_SOCKET,SO_REUSEADDR,1)) {
                echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
            }
            /*
            $ok = socket_bind($this->socket,$ip,$port);
            if($ok == false){
                echo "socket_bind() failed:reason:".socket_strerror(socket_last_error($this->socket));
                exit;
            }
            */
            $from = '';//响应ip
            $port = 0;//响应端口
            socket_recvfrom($this->socket, $buf,1024, 0, $from, $port);
            //echo '<br>'.bin2hex($buf);
            $decode = $this->decodeDomain(bin2hex($buf));//把ASCII码转为16进制，$buf为收到的报文内容
            return $decode;
        }
        public function encodeDomain($domain){
            $domain_array = explode(".",$domain);
            //$domain_str = '';
            $domain_str = (pack("n6",$this->id,0x0100,1,0,0,0));
            $this->query_length = 12;
            //$domain_str .= '0100';
            //$domain_str .= '0001000000000000';
            foreach($domain_array as $k => $v){
                $str_len = strlen($v);
                $domain_str .= pack("C",$str_len);
                $this->query_length += 1;
                for($i =0;$i < $str_len;$i ++){
                    $char = ord($v[$i]);     
                    $domain_str .= pack('C',$char);
                    $this->query_length += 1;
                }
            }
            $domain_str .= pack('C',0);
            $this->query_length += 1;
            $domain_str .= pack("n2",1,1);
            $this->query_length += 4;
            $encode_len = $this->query_length * 8;
            $encode = array();
            $encode['msg'] = $domain_str;
            $encode['len'] = $encode_len;
            return $encode;
        }
        public function decodeDomain($code){
            $ret = array(
                'status' => 0,
                'ra' => 0,
                'qtype' => 0,
                'cname' => '',
                'resnum' => 0,
                'ttl' => 0,
                'list' => array(
                    'ttl' => 0,
                    'ip' => ''
                )
            );
            $id = substr($code,0,4);
            $flag = substr($code,4,4);
            if($flag[3] == '0'){
                $ret['status'] = 1;//解析成功
            }else{
                return $ret;//解析失败
            }
            //echo $flag;
            $flag = base_convert($flag,16,2);//将16进制字符串转换为2进制字符串
            //echo $flag;
            if($flag[8] == '1'){
                $ret['ra'] = 1;//支持递归查询；
            }else {
                $ret['ra'] = 0;//不支持递归查询；
            }
            
            $resNum = hexdec(substr($code,12,4));//资源记录数；
            $ret['resnum'] = $resNum;
            
            $qtype = base_convert(substr($code,$this->query_length * 2 + 4,4),16,10);//将16进制转为10进制
            if($qtype == 1){
                $ret['qtype'] = 1;
                $ret['list'] = $this->decodeIp($code);
                return $ret;
            }else if ($qtype == 2){
                echo "NS记录";
            }else if($qtype ==5){
                $ttl = base_convert(substr($code,$this->query_length * 2 + 12,8),16,10);
                $data_length = base_convert(substr($code,$this->query_length * 2 + 20,4),16,10);
                $cname_data = substr($code,$this->query_length * 2 + 24,$data_length * 2);
                $cname = $this->decodeCname($code,$cname_data,$data_length);
                $ret['qtype'] = 5;
                $ret['ttl'] = $ttl;
                $ret['cname'] = $cname;
                $ip = array();
                for($j = 0;$j < $resNum - 1;$j ++){                   
                    $ip[$j] = $this->decodeIp($code);
                }
                $ret['list'] = $ip;
                return $ret;
            }else if ($qtype == 6){
                echo "SOA记录";
            }else if($qtype ==11){
                echo "WKS记录";
            }else if ($qtype == 12){
                echo "PRT记录";
            }else if($qtype ==15){
                echo "MX记录";
            }else if ($qtype == 33){
                echo "SRV记录";
            }else if($qtype ==38){
                echo "A6记录";
            }else if($qtype == 255){
                echo "任何资源记录";
            }                        
        }
        public function getId(){
            return rand(1,10000);
        }

        public function decodeCname($code,$data,$length){
            $domain = '';
            
            for($i = 0;$i < strlen($data);$i = $i+2){
                if(base_convert($data[$i].$data[$i + 1],16,10) < 48 || base_convert($data[$i].$data[$i + 1],16,10) > 122 ){
                    if($data[$i+1].$data[$i] == '0c'){
                        $position = base_convert($data[$i+2].$data[$i+3],16,10) * 2;
                        $offset = substr($code,$position);
                        $end = strpos($offset,'00');
                        $offset_domain = substr($code,$position,$end);
                        for($k = 0;$k<strlen($offset_domain);$k = $k+2){
                            if(base_convert($offset_domain[$k].$offset_domain[$k + 1],16,10) < 48 || base_convert($offset_domain[$k].$offset_domain[$k + 1],16,10) > 122 ){
                                $domain .= '.';
                            }else{
                                $domain .= pack("h*",$offset_domain[$k+1].$offset_domain[$k]);
                            }
                        }
                    }else{
                        $domain .= '.';
                    }
                }else{
                  //  echo "<br>".pack("h*",$data[$i+1].$data[$i]);
                    $domain .= pack("h*",$data[$i+1].$data[$i]);
                }

            }
            $this->offset += ( 24 + ($length * 2)); 
            $domain = trim($domain,'.');
            return $domain;
        }
        
        public function decodeIp($code){
            $this->offset += 12;
            $ttl = base_convert(substr($code,$this->query_length * 2 + $this->offset,8),16,10);
            $this->offset += 8;
            $data_length = base_convert(substr($code,$this->query_length * 2 + $this->offset,4),16,10);
            $this->offset += 4;
            $ip_data = substr($code,$this->query_length * 2 + $this->offset,$data_length * 2);
            $this->offset += $data_length * 2;
            $ip = '';
            for($i = 0; $i < strlen($ip_data);$i = $i +2){                
                $ip .= base_convert($ip_data[$i].$ip_data[$i + 1],16,10).'.';
            }
            $ip = trim($ip,'.');
            $res = array(
                'ttl' => $ttl,
                'ip' => $ip
            );
            return $res;
        }
        
        public function __destruct() {
            socket_close($this->socket);
        }
    }
?>