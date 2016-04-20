<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Kapasitasmhsbaru extends CI_Controller {
        
    private $limit;
    private $filter;
    private $order;
    private $offset;
    private $tabel;
    
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login')) 
        {
            redirect('ws');
        } else {
            $this->limit = $this->config->item('limit');
            $this->filter = $this->config->item('filter');
            $this->order = "id_sms asc";
            $this->offset = $this->config->item('offset');
            $this->tabel = 'daya_tampung';
            //load model and helper
            $this->load->model('m_feeder','feeder');
            $this->load->helper('directory');
            $this->load->helper('csv');
            $this->load->helper('function');
            //inisial config upload
            $config['upload_path'] = $this->config->item('upload_path');
            $config['allowed_types'] = $this->config->item('upload_tipe');
            $config['max_size'] = $this->config->item('upload_max_size');
            $this->load->library('upload',$config);
        }
    }
    
    public function index()
    {
        $this->view();
    }
    
    public function view($offset=0)
    {
        $data['tabel']=$this->tabel;
        $temp_dic = $this->feeder->getdic($this->session->userdata('token'), $this->tabel);
        $temp_rec = $this->feeder->getrset( $this->session->userdata('token'), 
                                            $this->tabel, 
                                            $this->filter, 
                                            $this->order, 
                                            $this->limit, 
                                            $offset
                                        );
        $temp_count = $this->feeder->count_all($this->session->userdata('token'), $this->tabel, $this->filter);
        //pagination
        $config['base_url'] = site_url('ws_dayatampung/view');
        $config['total_rows'] = $temp_count['result'];
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 3;
        $this->pagination->initialize($config);
        //laod config
        $data['pagination'] = $this->pagination->create_links();
        $data['offset'] = $offset;
        $data['listsdic'] = $temp_dic;
        $data['listsrec'] = $temp_rec;
        $data['total'] = $temp_count['result'];
        $data['url_add'] = 'index.php/ws_dayatampung/form_csv';
        tampil('intermediate/dayatampung/__data_tampung',$data);
    }
    
    public function form_createcsv()
    {
        $filter_sms= "id_sp = '".$this->session->userdata('id_sp')."'";
        $temp_prodi = $this->feeder->getrset($this->session->userdata('token'), 'sms', $filter_sms, $this->order, '', '');
        $data['prodi'] = $temp_prodi['result']; 
        $temp_smt = $this->feeder->getrset($this->session->userdata('token'), 'semester', 'a_periode_aktif =  1', '', '', '');
        $data['smt'] = $temp_smt['result'];                                                              
        $this->load->view('tpl/intermediate/dayatampung/__form_createcsv_dayatampung',$data);
    }

    public function createcsv()
    {
        $temp_dic = $this->feeder->getdic($this->session->userdata('token'), $this->tabel);
        $dumy_dic = $temp_dic['result'];
        $array = array();
        $header_mhs = array();
        foreach ($dumy_dic as $key) {
            $header_mhs[] = $key['column_name'];
        }
        $array[] = $header_mhs;
        $id_sp = $this->session->userdata('id_sp');
        $id_sms = $this->input->post('prodi');
        $id_smt = $this->input->post('smt');
        $separasi = $this->input->post('separasi');
        
        $sample = array($id_smt,
                        $id_sms,
                        'Target Mahasiswa Baru',
                        'Calon Ikut Seleksi',
                        'calon Lulus Seleksi',
                        'Daftar Sebagai Mahasiswa',
                        'Peserta Yang Mengundurkan Diri',
                        'Tanggal Awal Kuliah',
                        'Tanggal Akhir Kuliah',
                        'Jumlah Minggu Kuliah',
                        'Metode Kuliah',
                        'Metode Kuliah Eks');
        $array[] = $sample;
        $time = time();
        write_file('temps/'.$time.'_dayatampung.csv', array_to_csv($array,'',$separasi));
        echo "<div class=\"bs-callout bs-callout-success\">
                    File berhasil digenerate. <a href=\"".base_url()."temps/".$time."_dayatampung.csv\">Download</a>
            </div>";
    }

    public function form_csv()
    {
        $filter_sms= "id_sp = '".$this->session->userdata('id_sp')."'";
        $temp_prodi = $this->feeder->getrset($this->session->userdata('token'), 'sms', $filter_sms, $this->order, '', '');
        $data['prodi'] = $temp_prodi['result'];                                             
        $this->load->view('tpl/intermediate/dayatampung/__form_csv',$data);
    }

    public function dump_csv()
    {
        if (!$this->upload->do_upload()) 
        {
            echo "<div class=\"bs-callout bs-callout-danger\">".$this->upload->display_errors()."</div>";
        } else {            
            $file_data = $this->upload->data();
            $file_path = $this->config->item('upload_path').$file_data['file_name'];
            $separasi = $this->input->post('separasi');
            $csv_array = $this->csvimport->get_array($file_path,'','','',$separasi);
            if ($csv_array) {
                $temp_data = array();
                foreach ($csv_array as $value) 
                {
                    $temp_data[] = $value;
                }
                $temp_result = $this->feeder->insertrset($this->session->userdata['token'], $this->tabel, $temp_data);
                $sukses_count = 0;
                $error_count = 0;
                $error_msg = array();
                $i=0;
                if ($temp_result['result']) {
                    foreach ($temp_result['result'] as $key) {
                        ++$i;
                        if ($key['error_desc']==NULL) {
                            ++$sukses_count;
                        } else {
                            ++$error_count;
                            $error_msg[] = "<h4>Error</h4>".$key['error_desc']."";
                        }
                    }
                } else {
                    echo "<div class=\"alert alert-danger\" role=\"alert\"><h4>Error</h4>";
                    echo $temp_result['error_desc']."</div>";
                }
                if ((!$sukses_count==0) || (!$error_count==0)) {
                    echo "<div class=\"alert alert-warning\" role=\"alert\">Results (total ".$i." baris data):<br />
                            <font color=\"#3c763d\">".$sukses_count." data berhasil ditambah</font><br />
                                    <font color=\"#ce4844\" >".$error_count." data error (tidak bisa ditambahkan) </font>";
                                    if (!$error_count==0) {
                                        echo "<a data-toggle=\"collapse\" href=\"#collapseExample\" aria-expanded=\"false\" aria-controls=\"collapseExample\">
                                                Detail error
                                              </a>";    
                                    }
                                    echo "<div class=\"collapse\" id=\"collapseExample\">";
                                        foreach ($error_msg as $pesan) {
                                            echo "<div class=\"bs-callout bs-callout-danger\">
                                                    ".$pesan."
                                                  </div><br />";    
                                        }
                                    echo "</div>
                                </div>";
                }
            } else {
                echo "<div class=\"bs-callout bs-callout-danger\">Error: Tidak dapat mengekstrak file CSV. Silahkan dicoba kembali</div>";
            }
        }
    }

}

/* End of file ws_bobot.php */
/* Location: ./application/controllers/ws_bobot.php */