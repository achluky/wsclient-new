<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Skalanilai extends CI_Controller {
        
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
            $this->tabel = 'bobot_nilai';
            //load model and helper
            $this->load->model('m_feeder','feeder');
            $this->load->helper('directory');
            $this->load->helper('csv');
            $this->load->library('excel');
            $this->template = './template/skala_nilai_template.xlsx';
            //inisial config upload
            $config['upload_path'] = $this->config->item('upload_path');
            $config['allowed_types'] = $this->config->item('upload_tipe');
            $config['max_size'] = $this->config->item('upload_max_size');
            $config['encrypt_name'] = TRUE;

            $this->load->library('upload',$config);

            $temp_setting = read_file('setting.ini');
            $pecah = explode('#', $temp_setting);
            $this->dir_ws = $pecah[1];
            $this->host_ws = parse_url($this->dir_ws, PHP_URL_HOST);
            $this->port_ws = parse_url($this->dir_ws, PHP_URL_PORT);
            $ping = ping($this->host_ws,$this->port_ws);
            if (!$ping) {
                show_error('Error, Tidak bisa menghubungi server. Silahkan check koneksi LAN atau koneksi Internet Anda');
            }
        }
    }
    
    public function index()
    {
        $this->view();
    }
    
    public function view($offset=0)
    {
        $temp_dic = $this->feeder->getdic($this->session->userdata('token'), $this->tabel);
        $temp_sp = $this->session->userdata('id_sp');
        $temp_rec = $this->feeder->getrset( $this->session->userdata('token'), 
                                            $this->tabel, 
                                            $this->filter, 
                                            $this->order, 
                                            $this->limit, 
                                            $offset
                                        );
        $temp_count = $this->feeder->count_all($this->session->userdata('token'), $this->tabel, $this->filter);
        
        if (($temp_rec['error_desc']=='') && ($temp_sp=='') ){
            $this->session->set_flashdata('error','Kode PT Anda tidak ditemukan, silahkan masukkan kode PT anda dengan benar');
            redirect('welcome/setting');
        }
        $data['error_code'] = $temp_rec['error_code'];
        $data['error_desc'] = $temp_rec['error_desc'];
        $data['site_title'] = 'Daftar Skala Nilai';
        $data['title_page'] = 'Daftar Skala Nilai';
        $data['ket_page'] = 'Menampilkan dan mengelola data skala nilai';
        $data['assign_js'] = 'js/skalanilai_dt.js';
        $data['assign_modal'] = 'layout/modal_big_tpl.php';
        tampil('__bobot_nilai',$data);
    }
    
    public function uploadexcel()
    {
        $this->benchmark->mark('mulai');
        if (!$this->upload->do_upload()) {
            echo "<div class=\"bs-callout bs-callout-danger\">".$this->upload->display_errors()."</div>";
        } else {
            $file_data = $this->upload->data();
            $file_path = $this->config->item('upload_path').$file_data['file_name'];

            $objPHPExcel = PHPExcel_IOFactory::load($file_path);
            $objPHPExcel->setActiveSheetIndex(0);
            $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
            $jml_row = $objPHPExcel->getActiveSheet()->getHighestRow()-1;
            foreach ($cell_collection as $cell) {
                $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
                $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
                $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
                
                if ($row == 1) {
                    $header[$row][$column] = $data_value;
                } else {
                    $arr_data[$row][$column] = $data_value;
                }
            }
            if ($arr_data) {
                $temp_data = array();
                $sukses_count = 0;
                $sukses_msg = '';
                $error_count = 0;
                $error_msg = array();
                $sukses_msg = array();
                foreach ($arr_data as $key => $value) 
                {
                    $filter_sms = "kode_prodi='".$value['B']."' AND id_sp='".$this->session->userdata('id_sp')."'";
                    $temp_sms = $this->feeder->getrecord($this->session->userdata('token'),'sms',$filter_sms);
                    
                    if (count($temp_sms['result'])==0 ) {
                        echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4> Nilai <u>idsms</u> 
                                pada kode prodi <b>".$value['B']."</b> dan id_sp ".$this->session->userdata('id_sp')."tidak terdefinisikan! </div>";
                        die();
                    } else{
                        $id_sms = $temp_sms['result']['id_sms'];
                    }
                    
                    $temp_data['id_sms'] = $id_sms;
                    $temp_data['nilai_huruf'] = $value['C'];
                    $temp_data['bobot_nilai_min'] =$value['D'];
                    $temp_data['bobot_nilai_maks'] = $value['E'];
                    $temp_data['nilai_indeks'] = $value['F'];
                    $temp_data['tgl_mulai_efektif'] = $value['G'];
                    $temp_data['tgl_akhir_efektif'] = $value['H']; 

                    $temp_result = $this->feeder->insertrecord($this->session->userdata['token'], $this->tabel, $temp_data);
                    if ($temp_result['result']) {
                        if ($temp_result['result']['error_desc']==NULL) {
                            ++$sukses_count;
                            $sukses_msg[] = "<h4>Sukses</h4> </strong>/<strong>".$value['D']."</strong> berhasil ditambahkan";
                        } else {
                            ++$error_count;
                            $error_msg[] = "<h4>Error ".$temp_result['result']['error_code']." (".$value['D']." / ".$value['E'].")</h4>".$temp_result['result']['error_desc'];
                        }
                    } else {
                        echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error ".$temp_result['error_code']."</h4>".$temp_result['error_desc']."</div></div>";
                        break;
                    }
                }
                $this->benchmark->mark('selesai');
                $time_eks = $this->benchmark->elapsed_time('mulai', 'selesai');
                if ((!$sukses_count==0) || (!$error_count==0)) {
                    echo "Waktu eksekusi ".$time_eks." detik<br />
                            Results (total ".$jml_row." baris data):<br />
                            <font color=\"#3c763d\">".$sukses_count." data baru berhasil ditambah</font>";
                            if ($sukses_count!=0) {
                                echo "<a data-toggle=\"collapse\" href=\"#cols_sukses\" aria-expanded=\"false\" aria-controls=\"cols_sukses\"> Detail</a><br />";
                            } else { echo "<br />"; }
                            echo "<div class=\"collapse\" id=\"cols_sukses\">";
                                    foreach ($sukses_msg as $pesan_sukses) {
                                        echo "<div class=\"bs-callout bs-callout-success\">".$pesan_sukses."</div><br />";
                                    }
                            echo "</div>";

                    echo "<font color=\"#ce4844\" >".$error_count." data tidak bisa ditambahkan </font>";
                            if ($error_count!=0) {
                                echo "<a data-toggle=\"collapse\" href=\"#cols_error\" aria-expanded=\"false\" aria-controls=\"cols_error\">Detail error</a>";
                            }
                            echo "<div class=\"collapse\" id=\"cols_error\">";
                                            foreach ($error_msg as $pesan) {
                                                    echo "<div class=\"bs-callout bs-callout-danger\">".$pesan."</div><br />";
                                                }
                                    echo "</div>";
                }
            } else {
                echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>Tidak dapat mengekstrak file.. Silahkan dicoba kembali</div>";
            }
        }
    }

    public function createexcel()
    {
        
        $this->benchmark->mark('mulai');
        $temp_sp = $this->session->userdata('id_sp');
        if (!file_exists($this->template)) {
            echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>File template tidak tersedia.</div>";
        } else {
            //
            $data = array(
                        array('program_studi' => 'Fisika',
                            'nilai_huruf' => 'A',
                            'nilai_index' => '4.00',
                            'bobot_nilai_maximum' => '100',
                            'bobot_nilai_minimum' => '80',
                            'tanggal_mulai_efektif' => '2014-08-23',
                            'tanggal_akhir_efektif' => '2014-10-23'),
                        array('program_studi' => 'Matematika',
                            'nilai_huruf' => 'B',
                            'nilai_index' => '3.50',
                            'bobot_nilai_maximum' => '80',
                            'bobot_nilai_minimum' => '70',
                            'tanggal_mulai_efektif' => '2014-01-02',
                            'tanggal_akhir_efektif' => '2015-01-23'),
                    );
            $objPHPExcel = PHPExcel_IOFactory::load($this->template);

            //SET SHEET Mata Kuliah
            $objPHPExcel->setActiveSheetIndex(0);
            $baseRow = 3;
            foreach($data as $r => $dataRow) {
                $row = $baseRow + $r;
                $objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $r+1)
                                    ->setCellValue('B'.$row, $dataRow['program_studi'])
                                    ->setCellValue('C'.$row, $dataRow['nilai_huruf'])
                                    ->setCellValue('D'.$row, $dataRow['nilai_index'])
                                    ->setCellValue('E'.$row, $dataRow['bobot_nilai_maximum'])
                                    ->setCellValue('F'.$row, $dataRow['bobot_nilai_minimum'])
                                    ->setCellValue('G'.$row, $dataRow['tanggal_mulai_efektif'])
                                    ->setCellValue('H'.$row, $dataRow['tanggal_akhir_efektif']);
            }
            $objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);

            $filename = time().'-template-skalanilai.xlsx';
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $temp_tulis = $objWriter->save('temps/'.$filename);
            $this->benchmark->mark('selesai');
            $time_eks = $this->benchmark->elapsed_time('mulai', 'selesai');
            if ($temp_tulis==NULL) {
                echo "<div class=\"bs-callout bs-callout-success\">
                        File berhasil digenerate dalam waktu <strong>".$time_eks." detik</strong>. <br />Klik <a href=\"".base_url()."index.php/file/download/".$filename."\">disini</a> untuk download file
                    </div>";
            } else {
                echo "<div class=\"bs-callout bs-callout-danger\">
                        <h4>Error</h4>File tidak bisa digenerate. Folder 'temps' tidak ada atau tidak bisa ditulisi.
                    </div>";
            }
        }
        
    }

    public function json()
    {
        $search = $this->input->post('search');
        $sSearch = trim($search['value']);
        $orders = $this->input->post('order');
        $iStart = $this->input->post('start');
        $iLength = $this->input->post('length');

        $temp_limit = $iLength;
        $temp_offset = $iStart?$iStart : 0;
        $temp_total = $this->feeder->count_all($this->session->userdata('token'),$this->tabel,$this->filter);
        $totalData = $temp_total['result'];
        $totalFiltered = $totalData;

        if (!empty($sSearch)) {
            $temp_filter = "((nilai_huruf LIKE '%".$sSearch."%') OR (bobot_nilai_min LIKE '%".$sSearch."%') AND (p.id_sms=''))";
            $temp_rec = $this->feeder->getrset($this->session->userdata('token'),$this->tabel, $temp_filter,'nilai_huruf DESC',$temp_limit,$temp_offset);
            $__total = $this->feeder->count_all($this->session->userdata('token'),$this->tabel,$temp_filter);
            $totalFiltered = $__total['result'];
        } else {
            $temp_filter = "";
            $temp_rec = $this->feeder->getrset($this->session->userdata('token'),$this->tabel, $temp_filter,'id_sms ASC, nilai_huruf ASC',$temp_limit,$temp_offset);
        }

        $temp_error_code = $temp_rec['error_code'];
        $temp_error_desc = $temp_rec['error_desc'];

        if (($temp_error_code==0) && ($temp_error_desc=='')) {
            $temp_data = array();
            $i=0;
            foreach ($temp_rec['result'] as $key) {
                $temps = array();
                $temps[] = ++$i+$temp_offset;
                
                $filter_sms = "id_sms='".$key['id_sms']."'";
                $temp_sms = $this->feeder->getrecord($this->session->userdata('token'),'sms',$filter_sms);
                $nm_lemb = $temp_sms['result']['nm_lemb'];

                $temps[] = $nm_lemb;
                $temps[] = $key['nilai_huruf'];
                $temps[] = $key['bobot_nilai_min'];
                $temps[] = $key['bobot_nilai_maks'];
                $temps[] = $key['nilai_indeks'];
                $temps[] = $key['tgl_mulai_efektif'];
                $temps[] = $key['tgl_akhir_efektif'];
                $temp_data[] = $temps;
            }
            $temp_output = array(
                    'draw' => intval($this->input->get('draw')),
                    'recordsTotal' => intval( $totalData ),
                    'recordsFiltered' => intval( $totalFiltered ),
                    'data' => $temp_data
                );
            echo json_encode($temp_output);
        }
    }
}

/* End of file ws_bobot.php */
/* Location: ./application/controllers/ws_bobot.php */