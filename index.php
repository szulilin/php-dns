<?php
header("Content-type: text/html; charset=utf-8");
include 'dns.class.php';
if(isset($_GET['domain'])) {
    $dns = new dns();
    $domain = $_GET['domain'];
    $dns->send('211.162.78.1', 53, $domain);
    $res1 = $dns->recv();
    $Domain_IP1 = $res1['list'];
    $dns->send('211.162.78.2', 53, $domain);

    function arrayLevel($arr)
    {
        $al = array(0);
        function aL($arr, &$al, $level = 0)
        {
            if (is_array($arr)) {
                $level++;
                $al[] = $level;
                foreach ($arr as $v) {
                    aL($v, $al, $level);
                }
            }
        }

        aL($arr, $al);
        return max($al);
    }

    $arrayLevel = arrayLevel($Domain_IP1);


    $map = array(0 => '***.162.78.1',
        1 => '***.162.78.2',
        2 => '***.162.78.3',
        3 => '***.162.78.4'
    );

    $six = array(0 => '***.144.4.98',
                  1 => '***.144.4.106',
                  2 => '***.144.4.114',
                  3 => '***.144.4.27',
        4 => '***.144.4.35',
        5 => '***.144.4.43',
        6 => '***.144.4.145'
    );

    $seven = array(0 => '***.144.4.99'
    );
    for ($x = 0; $x < 4; $x++) {
        $row['host'] = $map[$x];
        $dns->send($map[$x], 53, $domain);
        $res = $dns->recv();
        $Domain_IP=$res['list'];
        $row['cname']=$res['cname'];
        $row['ip']=$Domain_IP;
        $rowlist[]=$row;
    }
    for ($i = 0; $i < 21; $i++) {
        $row6['host'] = $six[$i];
        $dns->send($six[$i], 53, $domain);
        $res6 = $dns->recv();
        $Domain_IP6=$res6['list'];
        $row6['cname']=$res6['cname'];
        $row6['ip']=$Domain_IP6;
        $rowlist6[]=$row6;
    }
    for ($j = 0; $j < 21; $j++) {
        $row7['host'] = $seven[$j];
        $dns->send($seven[$j], 53, $domain);
        $res7 = $dns->recv();
        $Domain_IP7=$res7['list'];
        $row7['cname']=$res7['cname'];
        $row7['ip']=$Domain_IP7;
        $rowlist7[]=$row7;
    }
   //  print_r($rowlist7);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title></title>
</head>
<body>
<table class="table" style="width:90%;margin-top:15px;">
    <form action="" method="get"  >
        <tr>
            <td align="right" valign="middle"><input type="text" style="width:250px;height:30px;font-size:20px;" name="domain" id="domain" placeholder="请输入域名" /> </td>
            <td align="left" >
                <input type="submit" name="submit" style="height:35px;font-size: 20px;background-color: mediumseagreen;color:white;font-family: '微软雅黑';border: 1px solid mediumseagreen;" value="检测DNS"/>
            </td>
        </tr>
    </form>
</table>
<table  border="1" style='width:99%;margin:0 auto;margin-top:15px;'>
    <tr>
        <th style="width:2%;">服务器ip</th>
        <th style="width:6%;">CNAME</th>
        <th style="width:10%;">解析IP值</th>
        <th style="width:8%;">ttl</th>
    </tr>
    <?php
    if(isset($_GET['domain'])){
    foreach($rowlist as $k => $v) { ?>
        <tr>
            <td><?php echo $v['host'];?></td>
            <td><?php echo $v['cname'];?></td>
            <td><?php
                if ($arrayLevel > 1) {
                    foreach ($row['ip'] as $value) {
                        echo $value['ip'] . "<br/>";
                    }
                } else {
                    print_r($row['ip']['ip']);
                }
                ?></td>
            <td><?php
                if(isset($_GET['domain'])) {
                    if ($arrayLevel > 1) {
                        foreach ($row['ip'] as $value) {
                            echo $value['ttl'] . "<br/>";
                        }
                    } else {
                        print_r($row['ip']['ttl']);
                    }
                }
                ?>
            </td>
        </tr>
    <?php } }?>
</table>
<table  border="1" style='width:99%;margin:0 auto;margin-top:15px;'>
    <tr>
        <th style="width:2%;">服务器ip</th>
        <th style="width:6%;">CNAME</th>
        <th style="width:10%;">解析IP值</th>
        <th style="width:8%;">ttl</th>
    </tr>
    <?php
    if(isset($_GET['domain'])){
        foreach($rowlist6 as $k => $v) { ?>
            <tr>
                <td><?php echo $v['host'];?></td>
                <td><?php echo $v['cname'];?></td>
                <td><?php
                    if ($arrayLevel > 1) {
                        foreach ($row6['ip'] as $value) {
                            echo $value['ip'] . "<br/>";
                        }
                    } else {
                        print_r($row6['ip']['ip']);
                    }
                    ?></td>
                <td><?php
                    if(isset($_GET['domain'])) {
                        if ($arrayLevel > 1) {
                            foreach ($row6['ip'] as $value) {
                                echo $value['ttl'] . "<br/>";
                            }
                        } else {
                            print_r($row6['ip']['ttl']);
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php } }?>
</table>
<table  border="1" style='width:99%;margin:0 auto;margin-top:15px;'>
    <tr>
        <th style="width:2%;">服务器ip</th>
        <th style="width:6%;">CNAME</th>
        <th style="width:10%;">解析IP值</th>
        <th style="width:8%;">ttl</th>
    </tr>
    <?php
    if(isset($_GET['domain'])){
        foreach($rowlist7 as $k => $v) { ?>
            <tr>
                <td><?php echo $v['host'];?></td>
                <td><?php echo $v['cname'];?></td>
                <td><?php
                    if ($arrayLevel > 1) {
                        foreach ($row7['ip'] as $value) {
                            echo $value['ip'] . "<br/>";
                        }
                    } else {
                        print_r($row7['ip']['ip']);
                    }
                    ?></td>
                <td><?php
                    if(isset($_GET['domain'])) {
                        if ($arrayLevel > 1) {
                            foreach ($row7['ip'] as $value) {
                                echo $value['ttl'] . "<br/>";
                            }
                        } else {
                            print_r($row7['ip']['ttl']);
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php } }?>
</table>
</body>
</html>
