<?php
class cate extends CI_Controller{
    protected $_OrderedCategory;
    function __construct(){
        parent::__construct();
        $this->load->helper("url");
        $this->load->library("form_validation");
        $this->load->model("cate_model");
        session_start();
        if( ! isset($_SESSION['user'])){
            redirect(base_url("administrator/user/login"));
        }

    } // end __construct

    public function index(){
        $this->listcate();

    } // end index()

    // List Category
    // Writen by HoangHH
    public function listcate(){
        $rawList = $this->cate_model->getAll();
        $orderList = array();

        $_SESSION['listedByID'] = array();

        foreach ($rawList as $key => $cateDetail) {
            $cate_id     = $cateDetail['cate_id'];
            $cate_name   = $cateDetail['cate_name'];
            $cate_parent = $cateDetail['cate_parent'];
            $cate_order  = $cateDetail['cate_order'];
            
            $strLevel = "";
            if(!in_array($cate_id, $_SESSION['listedByID'])){
                $_SESSION['listedByID'][] = $cate_id;

                $orderList[] = array(
                    'cate_id' => $cate_id,
                    'cate_name' => $strLevel . $cate_name,
                    'cate_parent' => $cate_parent,
                    'cate_order' => $cate_order
                    );
                $this->recursive($cate_id,$rawList,$strLevel,$orderList);

            } // end if (!inarray)
            

        } // end foreach
        // echo "</table>";

        $data['orderList'] = array_merge($orderList);

        $data['template'] = "cate/listcategory";
        $this->load->view('layout/layout',$data);
            
    } // end listcate()

    private function recursive($cate_id_parent,$rawList,$strLevel,&$orderList){
        $strLevel .= "____   ";
        // $a = 'b';
        // $UnOrdered = array();
        foreach ($rawList as $key => $cateDetail) {
            $cate_id     = $cateDetail['cate_id'];
            $cate_name   = $cateDetail['cate_name'];
            $cate_parent = $cateDetail['cate_parent'];
            $cate_order  = $cateDetail['cate_order'];

            if($cate_parent == $cate_id_parent){
  
                if(!in_array($cate_id, $_SESSION['listedByID'])){

                    $temp = array(
                    'cate_id' => $cate_id,
                    'cate_name' => $strLevel . $cate_name,
                    'cate_parent' => $cate_parent,
                    'cate_order' => $cate_order
                    );

                    $orderList[] = $temp;
                    // $UnOrdered[] = $temp;
                    $_SESSION['listedByID'][] = $cate_id;

                    $this->recursive($cate_id,$rawList,$strLevel,$orderList);

                } // end if
                
                
            } // end if $cate_parent
        } // end foreach
    } // end recursive()

    // Insert Category (account 5)
    // Writen by HoangHH
    public function insertCategory(){

        // get information exist category
        $rawList = $this->cate_model->getAll();
        $orderList = array();
        $_SESSION['listedByID'] = array();

        foreach ($rawList as $key => $cateDetail) {
            $cate_id     = $cateDetail['cate_id'];
            $cate_name   = $cateDetail['cate_name'];
            $cate_parent = $cateDetail['cate_parent'];
            $cate_order  = $cateDetail['cate_order'];
            
            $strLevel = "";
            if(!in_array($cate_id, $_SESSION['listedByID'])){
                $_SESSION['listedByID'][] = $cate_id;

                $orderList[] = array(
                    'cate_id' => $cate_id,
                    'cate_name' => $strLevel . $cate_name,
                    'cate_parent' => $cate_parent,
                    'cate_order' => $cate_order
                    );
                $this->recursive($cate_id,$rawList,$strLevel,$orderList);

            } // end if (!inarray)
        } // end foreach

        // list Category name for insert
        $ListInsert = array();
        foreach ($orderList as $key => $value) {
            // $CateID = $value['cate_id'];
            $ListInsert[] = array(
                'cate_id' => $value['cate_id'],
                'cate_name' => $value['cate_name']
                );
        } // 

        // echo "<pre>";
        // print_r($ListInsert);
        $data['ListInsert'] = array_merge($ListInsert);
        $data['template'] = "cate/insertcategory";
        
        $DataCate = array();
        if ($this->input->post('btnok')){
            $this->form_validation->set_rules('cate_name','Tên Category', 'required');
            // $this->form_validation->set_rules('cate_order','Thứ tự');
            
            $this->form_validation->set_message("required","%s không được bỏ trống");
            $this->form_validation->set_error_delimiters("<span class='error'>","</span>");

            if($this->form_validation->run()){
                $DataCate = array(
                        'cate_name'           => $this->input->post('cate_name'),
                        'cate_parent'          => $this->input->post('cate_parent'),
                        'cate_order'           => $this->input->post('cate_order')
                        ); // end array
                // echo "<pre>";
                // print_r($DataCate);
                $this->cate_model->insert($DataCate);
                redirect(base_url("administrator/cate/listcate"));
            } // end from_validation->run()

        } // end isset btnok

        $this->load->view("layout/layout",$data);
    } // end insertCategory()


    // VietDq
    public function update()
    
    {
      
        $id = $this->uri->segment(4);
        $data['categoryInfo'] = $this->cate_model->detail($id);
        $rows = $this->cate_model->detail($id);
      
        
        $data['showCategory'] = $this->getDataInsertCategory(0);
      
        if($this->input->post("ok")){
            $this->form_validation->set_rules("cate_name","Tên category","trim|required");
            
            $this->form_validation->set_rules("cate_parent","Tên category cha","trim|required|numeric");
            $this->form_validation->set_rules("cate_order","Thứ tự category ","trim|required|numeric");

            $this->form_validation->set_message("required","%s không được bỏ trống");

            $this->form_validation->set_message("numeric","%s phải là số so");
            $this->form_validation->set_error_delimiters("<span class='error'>","</span>");
            if($this->form_validation->run()){
                $parentname=$this->input->post("cate_parent");
                $cate_name=$this->input->post("cate_name");
                $listall=$this->cate_model->getAll();
                
                foreach ($listall as $row) {
                if (in_array(trim($cate_name),$row)&& $row['cate_id']!=$id) $data['errorName']="Đã tồn tại";
                if ($row['cate_parent']==$id &&$this->check($id,$parentname)==1) $data['errorTrung']="Không được chọn con của nó"; 
                //check bi lap vo tan

                }   
                if ($parentname==$rows['cate_id']) $data['errorTrung']="Không được chọn";
    
                if (!isset($data['errorTrung'])&& !isset($data['errorName'])) {
                
                $datacategory = array(
                                "cate_name"=>$this->input->post("cate_name"),
                                "cate_parent"=>$this->input->post("cate_parent"),
                                "cate_order"=>$this->input->post("cate_order")
                            );
                
                $this->cate_model->update($datacategory,$id);
                redirect(base_url("administrator/cate/listcate"));
                 }
                 }
            
        }
        $data['template'] = "cate/update";
        $this->load->view("layout/layout",$data);
        
    } // end update()

    private function getDataInsertCategory($parent = 0,$gach = '-  ',$data = NULL)
    {
        if(!$data) $data = array();  
        $sql = $this->cate_model->detailparent($parent);
        foreach($sql as $key=>$value){
            $data[] = array(
                        'cate_id'    =>$value['cate_id'],
                        'cate_name'  =>$gach.$value['cate_name'],
                        'cate_parent'=>$value['cate_parent']
                        );
            $data = $this->getDataInsertCategory($value['cate_id'],$gach.'---   ',$data);
        }    
        return $data;
    }

    public function check($parent,$child) {
      
      
      $info=$this->cate_model->infoparent($child);

       $parentid=$info['cate_parent'];
       if ($parent==0) return 1;
       if ($parentid==$parent) {return 1; }
       else if ($parentid!=0)
           return $this->check($parent,$parentid);
       else if ($parentid==0) return 0;
    }

    // Huan DT
    public function delete(){
        $cate_id = $this->uri->segment(4);
        $data = $this->cate_model->getAll();
        $detail = $this->cate_model->getOnce($cate_id);
       // print_r($detail);
        foreach($data as $value){
           if($value['cate_parent'] == $cate_id){
                $value['cate_parent'] = $detail['cate_parent'];
                $dta = array(
                'cate_parent' => $value['cate_parent']
            );
                echo $dta['cate_parent'];
            $this->cate_model->update($dta, $value['cate_id']);
           }


        }

        $this->cate_model->delete($cate_id);
            
        redirect(base_url("administrator/cate/listcate"));
    } // end delete
    

} // end class cate
// end file cate.php