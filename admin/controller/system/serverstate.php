<?php
class ControllerSystemServerstate extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载admin Model
        $this->load->library('sys_model/admin', true);
        $this->load->library('sys_model/rbac', true);

    }

    /**
     * 管理员列表
     */
    public function index() {

        $arr = $this->get_used_status();
        $this->assign('data', $arr);
        $this->response->setOutput($this->load->view('system/server_state', $this->output));
    }

    public function get_used_status(){
        $fp = popen('top -b -n 1 | grep -E "^(Cpu|Mem|Tasks|Swap)"',"r");//获取某一时刻系统cpu和内存使用情况
        $rs = "";
        while(!feof($fp)){
            $rs .= fread($fp,1024);
        }

        pclose($fp);
        $sys_info = explode("\n",$rs);
        $tast_info = explode(",",$sys_info[0]);//进程 数组
        $cpu_info = explode(",",$sys_info[1]);  //CPU占有量  数组
        $mem_info = explode(",",$sys_info[2]); //内存占有量 数组
        $swap_info = explode(",",$sys_info[3]); //内存占有量 数组
        //正在运行的进程数
        $tast_running = trim(trim($tast_info[1],'running'));


        //CPU占有量
        $cpu_usage = trim(trim($cpu_info[0],'Cpu(s): '),'%us');  //百分比

        //内存占有量
        $mem_total = trim(trim($mem_info[0],'Mem: '),'k total');
        $true_mem_used = $mem_total - trim($mem_info[2],'k free') - trim($mem_info[3],'k buffers') - trim($swap_info[3],'k cached');
        $mem_used = trim($mem_info[1],'k used');
        $mem_usage = round(100*intval($true_mem_used)/intval($mem_total),2);  //百分比


        $fp = popen('df -lh | grep -E "^(/)"',"r");
        $rs = fread($fp,1024);
        pclose($fp);
        $rs = preg_replace("/\s{2,}/",' ',$rs);  //把多个空格换成 “_”
        $hd = explode(" ",$rs);
        $hd_arr = array();
        $totals = floor(count($hd)/5);
        for($i = 0; $i < $totals; $i++ ){
            $hd_arr[] = array(
                'free' =>   $hd[3+$i*5],
                'usepae' =>   $hd[4+$i*5]
            );
        }
        $hd_avail = trim($hd[3],'G'); //磁盘可用空间大小 单位G
        $hd_usage = trim($hd[4],'%')."%"; //挂载点 百分比
        //print_r($hd);

        //检测时间
        $fp = popen("date +\"%Y-%m-%d %H:%M\"","r");
        $rs = fread($fp,1024);
        pclose($fp);
        $detection_time = trim($rs);
        $info = array('cpu_usage'=>$cpu_usage,'mem_total' => $mem_total, 'mem_used' => $mem_used, 'mem_usage'=>$mem_usage,'hd_avail'=>$hd_avail,'hd_usage'=>$hd_usage,'tast_running'=>$tast_running,'detection_time'=>$detection_time,'true_mem_used' => $true_mem_used,'hd_arr' => $hd_arr );

        return $info;
 }


    function ww(){
        // MEMORY
        if (false === ($str = @file("/proc/meminfo"))) return false;
        $str = implode("", $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

        $res['memTotal'] = round($buf[1][0]/1024, 2);
        $res['memFree'] = round($buf[2][0]/1024, 2);
        $res['memBuffers'] = round($buffers[1][0]/1024, 2);
        $res['memCached'] = round($buf[3][0]/1024, 2);
        $res['memUsed'] = $res['memTotal']-$res['memFree'];
        $res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memUsed']/$res['memTotal']*100,2):0;

        $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
        $res['memRealFree'] = $res['memTotal'] - $res['memRealUsed']; //真实空闲
        $res['memRealPercent'] = (floatval($res['memTotal'])!=0)?round($res['memRealUsed']/$res['memTotal']*100,2):0; //真实内存使用率

        $res['memCachedPercent'] = (floatval($res['memCached'])!=0)?round($res['memCached']/$res['memTotal']*100,2):0; //Cached内存使用率

        $res['swapTotal'] = round($buf[4][0]/1024, 2);
        $res['swapFree'] = round($buf[5][0]/1024, 2);
        $res['swapUsed'] = round($res['swapTotal']-$res['swapFree'], 2);
        $res['swapPercent'] = (floatval($res['swapTotal'])!=0)?round($res['swapUsed']/$res['swapTotal']*100,2):0;

    }

}


