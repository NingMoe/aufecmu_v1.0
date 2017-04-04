<?php
/**
 * Created by PhpStorm.
 * User: WeiZeng
 * Date: 2016/8/6
 * Time: 17:24
 * 这里是urp教务系统的信息处理库
 */
namespace Home\Handle;
class Info1Handle{

    private $url;
    private $curl;
    private $cookie;
    private $yzmPath;
    private $cache;


    public function __construct() {
        $this->url=C("DEFAULT_URL");
        $this->yzmPath=C("DEFAULT_YZM");
        $this->cache=C("ABSOLUTE_CACHE");
        $this->curl=D("Curl","Logic");
    }

    //无法验证码识别的时候做的事情
    public function loginInit() {
        srand((double)microtime()*100000);
        $random=rand(0,1000000);
        $yzmFileName=$this->yzmPath."$random.jpg";
        $cookieFile=$this->cache."$random.txt";
        $setArr=array(
            CURLOPT_URL=>$this->url."validateCodeAction.do?random=$random",
            CURLOPT_HEADER=>0,
            CURLOPT_COOKIEJAR=>$cookieFile,
        );
        $this->curl->setOpt($setArr);
        $img=$this->curl->getCurlResult();
        $this->handleCookie($cookieFile);
        $this->imgWrite($yzmFileName,$img);
        return $yzmFileName;
    }

    //进入教务系统
    public function entranceJw($zjh,$mm,$v_yzm) {
        $cookie=session("JSESSIONID");
        $setArr=array(
            CURLOPT_URL=>$this->url."loginAction.do",
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>"zjh1=&tips=&lx=&evalue=&eflag=&fs=&dzslh=&zjh=$zjh&mm=$mm&v_yzm=$v_yzm",
            CURLOPT_COOKIE=>'JSESSIONID='.$cookie,
            CURLOPT_REFERER=>$this->url
        );
        $this->curl->setOpt($setArr);
//		$content = $this->curl->getCurlResult();
//        return $content;
        $content=iconv('GBK', 'UTF-8', $this->curl->getCurlResult());
        if(strpos($content,'<span id="password_label">')!=false){
            if(strpos($content,('你输入的验证码错误，请您重新输入！'))!=false){
                return 3;
            }
            else if(strpos($content,('您的密码不正确，请您重新输入！'))!=false){
                return 2;
            }
            else {//此证件号不存在
                return -1;
            }
        }
        else{
            return 1;
        }
    }

    //自动加载
    public function autoEntre($zjh,$mm) {
        //获取验证码，尝试两次
        for($i=0;$i<3;++$i){
            $res=$this->getYzmInfo();
            // var_dump($res);
            if($res['status']==1){
                $status=$this->entranceJw($zjh,$mm,$res['result']);
//                return $status;
                if($status==1){
                    return 1;//正确
                }
            }
            else{
                if($i==1){
                    return -2;//两次验证码都未能识别成功,返回-2
                }
            }
        }
        return $res['status'];//将最终错误结果返回
    }

    //获取验证码识别信息
    public function getYzmInfo(){
        srand((double)microtime()*1000000);
        for($i=0;$i<2;++$i){
            $random =rand(0,1000000);
            $extArr=array(
                'yzmUrl'=>$this->url."validateCodeAction.do?random=$random",
                'type'=>'7',
                'yzmType'=>'.jpg',
                'sysType'=>'1',
            );
            $res=json_decode(D("JudgeSign","Logic")->getContent($extArr),true);
            // var_dump($res);
            if(!empty($res['result']) && strlen($res['result'])==4 ){
                //这里的result存在，说明验证码识别是成功的
                session("JSESSIONID",$res['cookie']['cookie1']);
                $this->cookie=$res['cookie']['cookie1'];//设置cookie
                //返回验证码识别结果
                return array(
                    'status'=>1,
                    'result'=>$res['result']
                );
            }
        }
        return array(
            'status'=>-1,
        );
    }

    //抓取课表信息
    public function getTimetable($cookie=null){
        $cookie=session("JSESSIONID");
        $setArr=array(
            CURLOPT_URL=>$this->url.'xkAction.do?actionType=6',
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_COOKIE=>'JSESSIONID='.$cookie,
            CURLOPT_HEADER=>0,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        return iconv("GBK","UTF-8",$this->curl->getCurlResult());
    }

    //抓取成绩信息
    public function getScore($cookie=null){
        $cookie=session("JSESSIONID");
        $setArr=array(
            CURLOPT_URL=>$this->url.'bxqcjcxAction.do',
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_COOKIE=>'JSESSIONID='.$cookie,
            CURLOPT_HEADER=>0,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        return iconv("GBK","UTF-8",$this->curl->getCurlResult());
    }


    //抓取考场信息
    public function getExamroom($cookie=null){
        $cookie=session("JSESSIONID");
        $setArr=array(
            CURLOPT_URL=>$this->url.'ksApCxAction.do?oper=getKsapXx',
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_COOKIE=>'JSESSIONID='.$cookie,
            CURLOPT_HEADER=>0,
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        //return $this->curl->getCurlResult();
        return iconv("GBK","UTF-8",$this->curl->getCurlResult());
    }

    //抓取个人信息
    public function getBasicInfo($cookie=null) {
        $cookie=session("JSESSIONID");
        $setArr=array(
            CURLOPT_URL=>$this->url.'xjInfoAction.do?oper=xjxx',
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_COOKIE=>'JSESSIONID='.$cookie,
            CURLOPT_HEADER=>0,
            CURLOPT_REFERER=>$this->url
        );
        $this->curl->reInit();
        $this->curl->setOpt($setArr);
        return iconv("GBK","UTF-8",$this->curl->getCurlResult());
    }


    //以下是抓取的信息处理函数====================================
    //初步处理课程表接口
    public function handleTimetable($content){
        $content=explode('<table width="100%" border="0" cellpadding="0" cellspacing="0" class="titleTop2">', $content);
        $content=$content[2];
        $arr=array(
            'first'=>array(),
            'second'=>array(),
            'third'=>array(),
            'fourth'=>array(),
            'fifth'=>array()
        );
        $result=array();
        $index=0;
        if(strpos($content, '<td rowspan="1" >')!=false){
            $arr['first']=$this->_Timetable($content,1);//预处理
            $result=array_merge($result,$this->handleTimetableArr($arr['first'],1,$index));//再处理
        }
        if(strpos($content, '<td rowspan="2" >')!=false){
            $arr['second']=$this->_Timetable($content,2);
            $result=array_merge($result,$this->handleTimetableArr($arr['second'],2,$index));
        }
        if(strpos($content, '<td rowspan="3" >')!=false){
            $arr['third']=$this->_Timetable($content,3);
            $result=array_merge($result,$this->handleTimetableArr($arr['third'],3,$index));
        }
        if(strpos($content, '<td rowspan="4" >')!=false){
            $arr['fourth']=$this->_Timetable($content,4);
            $result=array_merge($result,$this->handleTimetableArr($arr['fourth'],4,$index));
        }
        if(strpos($content, '<td rowspan="5" >')!=false){
            $arr['fifth']=$this->_Timetable($content,5);
            $result=array_merge($result,$this->handleTimetableArr($arr['fifth'],5,$index));
        }
        return $result;
    }

    //处理成绩信息
    public function handleScore($content){
        if(strpos($content, '<strong><font color="#990000">')!=false && strpos($content, '请您登录后再使用')==false ){
            return -2;//未完成评教
        }
        $content=explode('<td class="pageAlign">',$content);
        $content=explode('</form>',$content[1]);
        $content=explode('<td align="center">',$content[0]);
        $content_old=$content;
        $item=array();
        for($i=1;$i<count($content);++$i){
            $it=explode('</td>',$content[$i]);
            $item[$i]=trim($it[0]);
        }
        //return $item;
        $retArr=array();
        for($i=0;$i<count($item)/12;++$i){
            $retArr[$i]=array(
                'oldid'=>$item[1+$i*12],
                'km'=>$item[3+$i*12],
                'cj'=>$item[10+$i*12],
                'pm'=>$item[11+$i*12],
                'gpa'=>getGpa($item[10+$i*12]),
            );
        }
        if(strpos($retArr[1]['oldid'],"img") != false){
            return $this->handleScore_old($content_old);
        }
        return $retArr;
    }

    //兼容旧版教务的核心成绩抓取类库
    private function handleScore_old($content){
        $item=array();
        for($i=1;$i<count($content);++$i){
            $it=explode('</td>',$content[$i]);
            $item[$i]=trim($it[0]);
        }
        $retArr=array();
        for($i=0;$i<count($item)/13;++$i){
            $retArr[$i]=array(
                'oldid'=>$item[1+$i*13],
                'km'=>$item[3+$i*13],
                'cj'=>$item[10+$i*13],
                'pm'=>$item[11+$i*13],
                'gpa'=>getGpa($item[10+$i*13]),
            );
        }
        return $retArr;
    }


    //处理考场信息
    public function handleExamroom($content){
        $content=explode('<html>',$content);
        $content=explode('</html>',$content[1]);
        $content=explode('</thead>',$content[0]);
        $content=explode('</table>',$content[1]);
        $content=explode('<tr class="odd">',$content[0]);
        $arr=array();
        for($i=1;$i<count($content);++$i){
            $it=$content[$i];
            $item=explode('<td>',$it);
            $arr[$i-1]=array(
                'km'=>$this->deletetd($item[5]),
                'sj'=>$this->scoreDate($this->deletetd($item[6]),$this->deletetd($item[7]),$this->deletetd($item[8])),
                'wz'=>$this->site($this->deletetd($item[2]),$this->deletetd($item[3]),$this->deletetd($item[4])),
                'xq'=>$this->dqxnxq
            );
        }
        return $arr;
    }

    /*
     * 考试评教
     * */
    public function handleEva($cookie=null){
        if(empty($cookie)){
            $cookie=empty($this->cookie) ? session("JSESSIONID") : $this->cookie;
        }
        $content=$this->GetEvalution($cookie);
        for($i=1;$i<=count($content);++$i)
        {
            if($i%5==0)
            {
                if($content[$i]!="成绩未提交")
                {
                    if($content[$i-1]=="否")
                    {
                        $it=explode('<img name="', $content[$i]);
                        $it=explode('"', $it[1]);
                        $this->Evalution( $cookie, $it[0]);//执行一键评教；
                    }

                }
            }
        }
    }


    /*
     * 课表数据初步处理
     * $count表示传入课程数组在一周内有多少节课
     * example:高数在一个星期内会上两次，那么$count=2
     * */
    private function _Timetable($content,$count){
        $explodeStr='<td rowspan="'.$count.'" >';
        $explodeCount=8+($count-1)*7;
        $array=array();
        $arr = explode($explodeStr,$content);
        $count=count($arr);
        $jud=1;
        for($i=0,$index=0;$i<(int)($count/10);++$i,++$jud){
            for($jud==1 ? $j=0 :$j=1;$j<=9;++$index,++$j){
                $array[$index]=$this->deletetd($arr[$i*10+$j]);
            }
            $hou=$arr[($i+1)*10];
            $de=explode('<td', $hou,$explodeCount);
            for($k=0;$k<count($de);++$index,++$k){
                $array[$index]=$this->deletetd($de[$k]);
            }
        }
        $array[0]="";
        return $array;
    }

    /*
     * $arr表示传入的课程数组
     * $count表示传入课程数组在一周内有多少节课
     * example:高数在一个星期内会上两次，那么$count=2
     * $index表示最终返回数组的下标
     * */
    private function handleTimetableArr($arr,$count,&$index){
        //数组递增7   初始值为17
        $retArr=array();
        $sectionCount=17+($count-1)*7;//这个参数表示每一个数组中多少长度表示一节课
        for($i=0;$i<count($arr)/$sectionCount-1;++$i){//遍历数组
            for($j=0;$j<$count;++$j){//计算有多少节课
                $retArr[$index]=array(//
                    'oldtid'=>$arr[2+$i*$sectionCount],
                    'tname'=>$arr[3+$i*$sectionCount],
                    'tsite'=>$this->site($arr[15+$i*$sectionCount+$j*7],$arr[16+$i*$sectionCount+$j*7],$arr[17+$i*$sectionCount+$j*7]),
                    'tcount'=>$arr[14+$i*$sectionCount+$j*7],
                    'tweek'=>$arr[12+$i*$sectionCount+$j*7],
                    'tstart'=>$arr[13+$i*$sectionCount+$j*7],
                    'tsection'=>$this->section($arr[11+$i*$sectionCount+$j*7]),
                    'tsx'=>$this->tsx($arr[1+$i*$sectionCount],$arr[4+$i*$sectionCount],$arr[5+$i*$sectionCount],$arr[6+$i*$sectionCount],$arr[8+$i*$sectionCount]),
                );
                ++$index;
            }
        }
        return $retArr;
    }

    //用户信息处理及保存
    public function infoHandle($content){
        $content_old=$content;
        $arr=array();
        $jsonArr=array();
        //学号:&nbsp;
        $content=explode('学号:&nbsp;', $content ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $arr['studentid']=trim($content[0]);
        //姓名:&nbsp;
        $content=explode('姓名:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $arr['name']=trim($content[0]);
        //身份证号:&nbsp;
        $content=explode('身份证号:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $arr['sfz']=trim($content[0]);
        //性别
        $content=explode('性别:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $arr['sex']=trim($content[0]);
        /*
         * 学生类别:
            民族
            政治面貌:
            考区
            毕业中学:
         *
         * */
        $content=explode('学生类别:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $jsonArr['studentClass']=trim($content[0]);

        $content=explode('民族:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $jsonArr['nature']=trim($content[0]);

        $content=explode('政治面貌:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $jsonArr['political']=trim($content[0]);

        $content=explode('考区:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $jsonArr['exam']=trim($content[0]);

        $content=explode('毕业中学:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $jsonArr['highSchool']=trim($content[0]);

        //系所:&nbsp;
        $content=explode('系所:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $arr['college']=trim($content[0]);
        //专业:&nbsp;
        $content=explode('专业:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $arr['major']=trim($content[0]);
        //班级:&nbsp;
        $content=explode('班级:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $arr['class']=trim($content[0]);

        $content=explode('培养层次:&nbsp;', $content[1] ,2);//&nbsp;
        $content=explode('&nbsp;', $content[1] ,2);
        $content=explode('</td>', $content[1] ,2);
        $jsonArr['level']=trim($content[0]);
        $arr['other']=json_encode($jsonArr);
        if(empty($arr['name']))
            return $this->infoHandle_old($content_old);
        return $arr;
    }

    public function infoHandle_old($content){//$content变量由函数GetInfo获得
        $arr=array();
        $jsonArr=array();
        $content=explode('<td width="275">', $content,3);//</td>
        $content1=explode('</td>', $content[1],2);
        $arr['studentid']=trim($content1[0]);
        $content=explode('</td>', $content[2],2);
        $arr['name']=trim($content[0]);
        $content=explode(('身份证号:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $arr['sfz']=(trim($content[0]));//身份证号
        $content=explode(('性别:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $arr['sex']=trim($content[0]);//性别
        $content=explode(('学生类别:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['studentClass']=(trim($content[0]));//学生类别

        $content=explode(('学籍状态:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['SchoolRollState']=(trim($content[0]));//学籍状态

        $content=explode(('民族:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['nature']=(trim($content[0]));
        //----------
        $content=explode(('出生日期:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['birthday']=trim($content[0]);
        //----------
        $content=explode(('政治面貌:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['political']=(trim($content[0]));
        //----------
        $content=explode(('	考区:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['exam']=(trim($content[0]));
        //----------
        $content=explode(('毕业中学:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['highSchool']=(trim($content[0]));
        //----------
        $content=explode(('入学日期:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['admissionDate']=trim($content[0]);
        //----------
        $content=explode(('系所:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $arr['college']=(trim($content[0]));
        //----------
        $content=explode(('专业:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $arr['major']=(trim($content[0]));
        //----------
        $content=explode(('年级:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['grade']=(trim($content[0]));
        //----------
        $content=explode(('班级:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $arr['class']=(trim($content[0]));
        //----------
        $content=explode(('培养层次:'), $content[1],2);//<td align="left" width="275">
        $content=explode('<td align="left" width="275">', $content[1],2);
        $content=explode('</td>', $content[1],2);
        $jsonArr['level']=(trim($content[0]));
        $arr['other']=json_encode($jsonArr);
        if(empty($arr['name']))
            return null;
        return $arr;
    }

    /*
     * 获取该课程上课地点
     * */
    private function site($xq,$jxl,$js){
        $jxl=str_replace($xq,"",$jxl);//如果教学楼中存在校区，删掉
        $js=str_replace($jxl."-","",$js);//如果教室中存在教学楼，删掉
        return json_encode(array(
            'xq'=>$xq,//校区
            'jxl'=>$jxl,//教学楼
            'js'=>$js//教室
        ));
    }

    /*
     * 获取上课区间
     * */
    private function section($sectionStr){
        if($sectionStr=="1-17周上" || $sectionStr=="1-18周上" || $sectionStr=="1-19周上" || $sectionStr=="1-20周上" || $sectionStr=="1-21周上" || $sectionStr=="1-22周上" || $sectionStr=="1-16周上"){
            return 1;
        }
        if($sectionStr=="单周上课"){
            return 2;
        }
        if($sectionStr=="双周上课"){
            return 3;
        }
        if(strpos($sectionStr,"-") && strpos($sectionStr,"周")){
            $retStr="4|";
            $str=explode("-",$sectionStr);
            $retStr=$retStr.$str[0].",";
            $str=explode("周",$str[1]);
            return $retStr.$str[0];
        }
        return $sectionStr;
    }

    /*
     * 其他属性
     * */
    private function tsx($pyfa,$kxh,$xf,$sx,$js){
        return json_encode(array(
            'pyfa'=>$pyfa,
            'kxh'=>$kxh,
            'xf'=>$xf,
            'sx'=>$sx,
            'js'=>str_replace("*","",$js)//删除教师姓名后面的*
        ));
    }

    /*
     * 设置考试时间
     * */
    private function scoreDate($weekly,$week,$time){
        return json_encode(array(
            'weekly'=>$weekly."周",
            'week'=>"星期".$week,
            'time'=>$time,
        ));
    }


    public function GetEvalution($cookie)
    {
        $url_eva=$this->url."jxpgXsAction.do?oper=listWj";
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_eva);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, 'JSESSIONID='.$cookie);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $content = curl_exec($ch);
        curl_close($ch);
        $content=iconv('GBK', 'UTF-8', $content);
        if(strpos("非教学评估时期，或评估时间已过。", $content)!=false)
        {
            return false;
        }
        $content=explode('<td class="pageAlign">', $content);
        $content=explode('<div align="right">', $content[1]);
        $content=explode('<td align="center"', $content[0]);
        $arr=array();
        for($i=1;$i<count($content);++$i)
        {
            if($i%5==0)
            {
                $it=explode('</td>', $content[$i]);
                $it=explode('>', $it[0],2);
                $arr[$i]=$it[1];
            }
            else
            {
                $it=explode('</td>', $content[$i]);
                $it=explode('>', $it[0]);
                $arr[$i]=$it[1];
            }
        }
        return $arr;
    }

    public function Evalution($cookie,$Info)
    {
        $url_eva_pj=$this->url."jxpgXsAction.do";//评教具体地址
        $Info=explode('#@', $Info);
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_eva_pj);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, 'JSESSIONID='.$cookie);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        $data="wjbm=$Info[0]&bpr=$Info[1]&bprm=$Info[2]&wjmc=$Info[3]&pgnrm=$Info[4]&pgnr=$Info[5]&oper=wjShow&wjbz=null&pageSize=20&page=1&currentPage=1&pageNo=";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $content = curl_exec($ch);
        curl_close($ch);
        //var_dump($content);
        $content=explode('<tr align="left">', $content);
        $name=array();
        $value=array();
        $post_xuanxiang="";
        for($i=1;$i<count($content);++$i)
        {
            $name[$i]=$this->GetName($content[$i]);
            $value[$i]=$this->GetValue($content[$i]);
            $post_xuanxiang=$post_xuanxiang.$name[$i]."=".$value[$i]."&";
        }
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url."jxpgXsAction.do?oper=wjpg");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, 'JSESSIONID='.$cookie);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        $neirong=$this->GetNeirong();
        $Post="wjbm=$Info[0]&bpr=$Info[1]&pgnr=$Info[5]&xumanyzg=zg&zgpj=$neirong&$post_xuanxiang"."wjbz";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Post);
        $content = curl_exec($ch);
        curl_close($ch);
    }

    public function GetName($Content)
    {
        $Content=explode('<input type="radio" name="', $Content,2);
        $Content=explode('"', $Content[1],2);
        return $Content[0];
    }

    public function GetValue($Content)
    {
        $Content=explode('value="', $Content,2);
        $Content=explode('"', $Content[1],2);
        return $Content[0];
    }

    public function GetNeirong()
    {
        $arr=array();
        $arr[1]=charsetToGBK("老师上课很负责任，上课很有意思，充分调动了我们的积极性，给这个老师十分！不怕他骄傲！");
        $arr[2]=charsetToGBK("老师很专注，很有活力，很开朗，性格好好喔，是我喜欢的那种类型，给他八分，剩下两分怕他骄傲～");
        $arr[3]=charsetToGBK("老师对待我们就跟对待他的亲生孩子一样，无微不至的点到签到考试小测，很有责任感哦～");
        $rand=rand(1,3);
        return $arr[$rand];
    }





    //以下是信息处理类内部函数====================================
    private function deletetd($content){
        if(strpos($content, "&nbsp;")!=false){
            $content=str_replace("&nbsp;", "", $content);
        }
        $arr=explode("</td>", $content,2);
        return trim(str_replace(">", "", $arr[0]));
    }

    //处理cookie头文件
    private function handleCookie($cookieFile){
        $content = file_get_contents($cookieFile) ;
        $str=explode("JSESSIONID", $content);
        $this->cookie=trim($str[1]);
        session("JSESSIONID",$this->cookie);
        unlink($cookieFile);//删除cookieFile
    }

    //写入图片
    private function imgWrite($filename,$img){
        $fp = fopen($filename,"wb");
        fwrite($fp,$img);
        fclose($fp);
    }
}



/*
 * 这里是核心函数的推倒过程，不使用
 * */
//private function Timetable1($content){
//    $array=array();
//    $arr = explode('<td rowspan="1" >',$content);
//    $count=count($arr);
//    $jud=1;
//    for($i=0,$index=0;$i<(int)($count/10);++$i,++$jud){
//        for($jud==1 ? $j=0 :$j=1;$j<=9;++$index,++$j){
//            $array[$index]=$this->deletetd($arr[$i*10+$j]);
//        }
//        $hou=$arr[($i+1)*10];
//        $de=explode('<td', $hou,8);
//        for($k=0;$k<count($de);++$index,++$k){
//            $array[$index]=$this->deletetd($de[$k]);
//        }
//    }
//    $array[0]="";
//    return $array;
//}
//
//private function Timetable2($content){
//    $array=array();
//    $arr = explode('<td rowspan="2" >',$content);
//    $count=count($arr);
//    $jud=1;
//    for($i=0,$index=0;$i<(int)($count/10);++$i,++$jud){
//        for($jud==1 ? $j=0 :$j=1;$j<=9;++$index,++$j){
//            $array[$index]=$this->deletetd($arr[$i*10+$j]);
//        }
//        $hou=$arr[($i+1)*10];
//        $de=explode('<td', $hou,15);
//        for($k=0;$k<count($de);++$index,++$k){
//            $array[$index]=$this->deletetd($de[$k]);
//        }
//    }
//    $array[0]="";
//    return $array;
//}
//
//private function Timetable3($content){
//    $array=array();
//    $arr = explode('<td rowspan="3" >',$content);
//    $count=count($arr);
//    $jud=1;
//    for($i=0,$index=0;$i<(int)($count/10);++$i,++$jud){
//        for($jud==1 ? $j=0 :$j=1;$j<=9;++$index,++$j){
//            $array[$index]=$this->deletetd($arr[$i*10+$j]);
//        }
//        $hou=$arr[($i+1)*10];
//        $de=explode('<td', $hou,22);
//        for($k=0;$k<count($de);++$index,++$k){
//            $array[$index]=$this->deletetd($de[$k]);
//        }
//    }
//    $array[0]="";
//    return $array;
//}
//
//private function Timetable4($content){
//    $array=array();
//    $arr = explode('<td rowspan="4" >',$content);
//    $count=count($arr);
//    $jud=1;
//    for($i=0,$index=0;$i<(int)($count/10);++$i,++$jud){
//        for($jud==1 ? $j=0 :$j=1;$j<=9;++$index,++$j){
//            $array[$index]=$this->deletetd($arr[$i*10+$j]);
//        }
//        $hou=$arr[($i+1)*10];
//        $de=explode('<td', $hou,29);
//        for($k=0;$k<count($de);++$index,++$k){
//            $array[$index]=$this->deletetd($de[$k]);
//        }
//    }
//    $array[0]="";
//    return $array;
//}
//
//private function Timetable5($content){
//    $array=array();
//    $arr = explode('<td rowspan="5" >',$content);
//    $count=count($arr);
//    $jud=1;
//    for($i=0,$index=0;$i<(int)($count/10);++$i,++$jud){
//        for($jud==1 ? $j=0 :$j=1;$j<=9;++$index,++$j){
//            $array[$index]=$this->deletetd($arr[$i*10+$j]);
//        }
//        $hou=$arr[($i+1)*10];
//        $de=explode('<td', $hou,36);
//        for($k=0;$k<count($de);++$index,++$k){
//            $array[$index]=$this->deletetd($de[$k]);
//        }
//    }
//    $array[0]="";
//    return $array;
//}