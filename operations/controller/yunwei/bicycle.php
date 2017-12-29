<?php
/**
 *
 * User:
 * Date: 2017/7/21 0021
 * Time: 14:27
 */
class ControllerYunWeiBicycle extends Controller
{
    public function index(){

        $post_data = $this->request->post(array('bike_id', 'lng', 'lat', 'user_name'));

        if(!$post_data['bike_id']){
            $this->response->showErrorResult('单车ID不能为空', 901);
        }

        if(!$post_data['lng']){
            $this->response->showErrorResult('单车经度不能为空', 901);
        }

        if(!$post_data['lat']){
            $this->response->showErrorResult('单车纬度不能为空', 901);
        }

        //坐标转换
        library('tool/coordinate');
        $latLng = new \Tool\LatLng($post_data['lng'], $post_data['lat']);
        $gcLatLng = \Tool\Coordinate::gcj_wgs($latLng);
        $post_data['lng'] = $gcLatLng->lng;
        $post_data['lat'] = $gcLatLng->lat;

        #入库
        $this->load->library("sys_model/move_bicycle");
        $this->load->library("sys_model/lock");
        $this->load->library("sys_model/bicycle");
        $id_str = $post_data['bike_id'];
        $id_arr = explode(',',$id_str);
        $post_data['add_time'] = time();
        $num = 0;
        if(!empty($id_arr)){
            foreach($id_arr as $v){
                $post_data['bike_id'] = $v;
                $this->sys_model_move_bicycle->add($post_data);
                //修改单车的位置信息
                $w['bicycle_id'] = $v;
                $field = 'lock_id';
                $bicycle_info = $this->sys_model_bicycle->getBicycleInfo($w,$field);
                $updata = array(
                    'lng' => $post_data['lng'],
                    'lat' => $post_data['lat'],
                );
                $where = array('lock_id' => $bicycle_info['lock_id']);
                if($this->sys_model_lock->updateLock($where,$updata)){
                    $num++;
                };
                $up_bike = array(
                    'last_used_time' => time()
                );
                $this->sys_model_bicycle->updateBicycle($w,$up_bike);
            }
        }
        $this->response->showSuccessResult(array('total' => $num),'成功返回');


    }


}
