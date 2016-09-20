# php-dns
一个用php写的向指定dns服务器请求域名解析协议的程序。

    $dns = new dns();
    $dns->send('211.162.78.1', 53, "www.baidu.com");//第一个参数为dns服务器，53为端口，后面的为域名
    $res1 = $dns->recv();
    $Domain_IP1 = $res1['list'];//解析出的ip列表
