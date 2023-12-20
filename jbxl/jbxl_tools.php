<?php
//
// by Fumi.Iseki  2007/03/24
//                2012/04/12
//                2013/04/20
//                2013/09/21
//                2014/11/28
//                2016/05/26
//                2019/08/21
//                2022/07/06
//

$jbxl_tools_ver = 2022070600;


//
if (!defined('JBXL_TOOLS_VER') and !defined('_JBXL_TOOLS')) {

define('JBXL_TOOLS_VER', $jbxl_tools_ver);



/****************************************************************************************
//
// function  jbxl_to_subnetformats($strips)
// function  jbxl_match_ipaddr($ip, $ipaddr_subnets)
// function  jbxl_randstr($len=8, $lowcase=false)
//
// function  jbxl_get_ipresolv_url($ip)
//
// function  jbxl_get_url_params_array($urlstr)
// function  jbxl_get_url_params_str($params, $amp=false)
// function  jbxl_make_url($serverURI, $portnum=0)
//
*****************************************************************************************/


//
// IPアドレスを "," または半角空白で区切って記述した文字列から，有効なIPアドレス
// とサブネットを8bitずつ取り出す．CIDER対応
//
// 入力例：" 0.2.1.1/2 222.222.111.222/255., 123.31.6.000 202.26.156.2/20, 202.26.144.0/255.255.255.0   , "
//  
// 戻り値：
//     $return[index]['ipaddr'][0〜3の数]  8bit区切りの IPアドレス
//     $return[index]['subnet'][0〜3の数]  8bit区切りの netmaskアドレス
//
function  jbxl_to_subnetformats($strips)
{
    $return = array();

    $tmpips = preg_split("/[ ,]/ ", $strips);
    foreach($tmpips as $value) {
        if (!empty($value)) $ipfmts[] = $value;
    }
    if (empty($ipfmts)) return $return;

    unset($tempips);
    // omission of subnetmask
    foreach($ipfmts as $ipfmt) {
        $tempips = explode('/', $ipfmt);
        if (empty($tempips[0])) continue;
        if (empty($tempips[1])) {
            $ips = explode('.', $tempips[0]);
            $tempips[1] = '';
            for ($i=0; $i<4; $i++) {
                if (empty($ips[$i])) $tempips[1].= '0';
                else                 $tempips[1].= '255';
                if ($i!=3) $tempips[1].= '.';
            }
            unset($ips);
        }
        $ipaddr_subnets[] = $tempips;
    }
    if (empty($ipaddr_subnets)) return $return;

    //
    $index = 0;
    foreach($ipaddr_subnets as $ipaddr_subnet) {
        $ips = explode('.', $ipaddr_subnet[0]);
        $sub = explode('.', $ipaddr_subnet[1]);

        if (count($sub)==1 and $sub[0]<=32) {     // CIDER -> SubnetMask
            $cider = $sub[0];
            $nbyte = (int)($cider/8);
            $nbit  = $cider - $nbyte*8;
            for ($i=0; $i<$nbyte; $i++) {
                $sub[$i] = 255;
            }
            if ($nbyte!=4) {
                $nsub = 0;
                $base = 128;
                for ($i=0; $i<$nbit; $i++) {
                    $nsub += $base;
                    $base = $base/2;
                }    
                $sub[$nbyte] = $nsub;
            }
        }
        
        for ($i=0; $i<4; $i++) {
            if (!empty($ips[$i])) $return[$index]['ipaddr'][$i] = (int)$ips[$i]; 
            else                  $return[$index]['ipaddr'][$i] = (int)0;
            if (!empty($sub[$i])) $return[$index]['subnet'][$i] = (int)$sub[$i]; 
            else                  $return[$index]['subnet'][$i] = (int)0;
        }
        $index++;
    }

    return $return;
}


//
// $ip が $ipaddr_subnetsの中に含まれるか検査する．
// $ipaddr_subnets は jbxl_to_subnetformats()が出力したものを使用すること．
// $ip の内容の形式はチェックしない．これは呼び出し側の責任．
//
function  jbxl_match_ipaddr($ip, array $ipaddr_subnets)
{
    $ipa = explode('.', $ip);
    if (empty($ipa)) return false;

    for ($i=1; $i<4; $i++) {
        if (empty($ipa[$i])) $ipa[$i] = 0;
    }

    foreach($ipaddr_subnets as $ipaddr_subnet) {
        $ips = $ipaddr_subnet['ipaddr'];
        $sub = $ipaddr_subnet['subnet'];

        $match_f = true;
        for ($i=0; $i<4; $i++) {
            $check1 = $ipa[$i] & $sub[$i];
            $check2 = $ips[$i] & $sub[$i];
            if ($check1 != $check2) {
                $match_f = false;
                break;
            }
        }

        if ($match_f) {
            //print_r($ips);
            //print_r($sub);
            return true;
        }
    }
    return false;
}



$JBXLBaseChar = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";


function  jbxl_randstr($len=8, $lowcase=false)
{
    global $JBXLBaseChar;

    if ($lowcase) $rndmax = 25;
    else          $rndmax = strlen($JBXLBaseChar) - 1;

    $return  = "";
    for($i=0; $i<$len; $i++) {
        $randnum = mt_rand(0, $rndmax);
        $return .= substr($JBXLBaseChar, $randnum, 1);
        //$return .= $JBXLBaseChar{mt_rand(0, $rndmax)};  // Deprecated
    }
    return $return;
}


function  jbxl_get_ipresolv_url($ip, $region='APNIC')
{
    if (!preg_match('/(^\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip, $match)) return '';

    if ($match[1]>255 or $match[2]>255 or $match[3]>255 or $match[4]>255) return '';
    if ($match[1]=='127' or  $match[1]=='10') return '';
    if ($match[1]=='172' and $match[2]>='16' and $match[2]<='31') return '';
    if ($match[1]=='192' and $match[2]=='168') return '';

    if ($region=='JPNIC') {
        $url = 'http://whois.nic.ad.jp/cgi-bin/whois_gw?type=NET&key='.$ip;    // JPNIC
    }
    else {
        $url = 'http://wq.apnic.net/apnic-bin/whois.pl?searchtext='.$ip;    // APNIC
    }

    return $url;
}


function  jbxl_get_url_params_array($urlstr)
{
    $strs = explode('?', $urlstr);
    $paramstr = $strs[0];
    if (array_key_exists(1, $strs)) $paramstr = $strs[1];

    $ret = array();
    $params = explode('&', $paramstr);
    foreach($params as $param) {
        if (substr($param, 0, 4)=='amp;') $param = substr($param, 5);
        $temps = explode('=', $param);
        $ret[$temps[0]] = '';
        if (array_key_exists(1, $temps)) $ret[$temps[0]] = $temps[1];
    }
    
    return $ret;
}


//
// $params: パラメータの入っている配列
// $amp:    先頭文字を '&' にするか？ false の場合は 先頭文字は '?'
//
function  jbxl_get_url_params_str($params, $amp=false)
{
    $ret = '';
    if (!is_array($params)) return $ret;
    
    $no = 0;
    foreach($params as $key => $param) {
        if ($no==0 and !$amp) {
            $ret .= '?'.$key.'='.$param;
        }
        else {
            $ret .= '&amp;'.$key.'='.$param;
        }
        $no++;
    }
    return $ret;
}


//
// 入力された FSDN, URL に対して http(s)://ABC.EFG:#/ の形を生成する
//
function  jbxl_make_url($serverURI, $portnum=0)
{
    $url  = '';
    $host = 'localhost';
    $port = 80;
    $protocol = 'http';

    if ($serverURI!=null) {
        $uri = preg_split("/[:\/]/", $serverURI);

        // with http:// or https://
        if (array_key_exists(3, $uri)) {
            $protocol = $uri[0];
            $host = $uri[3];
            //
            if (array_key_exists(4, $uri)) {
                $port = $uri[4];
            }
            else {
                if ($portnum!=0) {
                    $port = $portnum;
                }
                else {
                    if      ($uri[0]=='http')  $port = 80;
                    else if ($uri[0]=='https') $port = 443;
                    else if ($uri[0]=='ftp')   $port = 21;
                    // else if ....
                }
            }
        }

        // with no http:// and https:// 
        else {
            $host = $uri[0];
            if (array_key_exists(1, $uri)) {
                $port = $uri[1];
            }
            else {
                if ($portnum!=0) { 
                    $port = $portnum;
                }
                else {
                    $port = 80;
               }
            }
        }

        //
        if ($port==443) {
            $url = 'https://'.$host.':'.$port.'/';
            $protocol = 'https';
        }
        else if ($port==80) {
            $url = 'http://'.$host.'/';
            $protocol = 'http';
        }
        else if ($port==21) {
            $url = 'ftp://'.$host.'/';
            $protocol = 'ftp';
        }
        else {
            $url = $protocol.'://'.$host.':'.$port.'/';
        }
    }

    $server['url']  = $url;
    $server['host'] = $host;
    $server['port'] = $port;
    $server['porotocol'] = $protocol;
    
    return $server;
}


}         // !defined('JBXL_TOOLS_VER')
