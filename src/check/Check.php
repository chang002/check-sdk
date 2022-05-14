<?php

namespace Chang002\CheckSdk\check;

class Check
{
    protected $check;

    function __construct($ApiKey,$SecretKey){
        $this->check=$this->getAccessToken($ApiKey, $SecretKey);
    }

    /**
     * 内容审核
     */
    public function contentCheck($content)
    {
//        $content = $request->post('content');
        if ($content) {
            //ApiKey和SecretKey从自己在百度智能云上创建的应用信息里获取
            $token = $this->check;

            $url = 'https://aip.baidubce.com/rest/2.0/solution/v1/text_censor/v2/user_defined?access_token=' . $token;

            $bodys = array(
                'text' => $content
            );

            $res = $this->curlPost($url, $bodys);

            //结果转成数组
            $res = json_decode($res, true);

            //根据自己的业务逻辑进行处理
            if ($res['conclusion'] == '合规') {
                //合规
                return 1;
            }
            //不合规
            return 0;
        }
        //空
        return -1;
    }

    /**
     * 图片审核
     */
    public function imageAudit($file)
    {

//        $fileTmp = $request->file('image')->getPathname();
        $fileTmp = $file;
        $token = $this->check;
        $url = 'https://aip.baidubce.com/rest/2.0/solution/v1/img_censor/v2/user_defined?access_token=' . $token;
        $img = file_get_contents($fileTmp);//本地路径
        $img = base64_encode($img);
        $bodys = array(
            'image' => $img
        );
        $res = $this->curlPost($url, $bodys);
        //结果转成数组
        $res = json_decode($res, true);
        //根据自己的业务逻辑进行处理
        return $res;
    }

    /**
     * CURL的Post请求方法
     * @param string $url
     * @param string $param
     * @return bool|string
     */
    function curlPost($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        // 初始化curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $postUrl);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // post提交方式
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        // 运行curl
        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }

    /**
     * 获取百度开放平台的票据
     * 参考链接：https://ai.baidu.com/ai-doc/REFERENCE/Ck3dwjhhu
     */
    public function getAccessToken($ApiKey = '', $SecretKey = '', $grantType = 'client_credentials')
    {

        $url = 'https://aip.baidubce.com/oauth/2.0/token';
        $post_data['grant_type'] = $grantType;
        $post_data['client_id'] = $ApiKey;
        $post_data['client_secret'] = $SecretKey;
        $o = "";
        foreach ($post_data as $k => $v) {
            $o .= "$k=" . urlencode($v) . "&";
        }
        $post_data = substr($o, 0, -1);

        $res = $this->curlPost($url, $post_data);
        //进行把返回结果转成数组
        $res = json_decode($res, true);
        if (isset($res['error'])) {
            exit('API Key或者Secret Key不正确');
        }
        $accessToken = $res['access_token'];
        return $accessToken;
    }
}
