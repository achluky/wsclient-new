<div class="page-header" style="margin-top: 10px;" >
    <div class="row">
        <div class="col-md-4">
            <h4>DATA KAPASITAS MAHASISWA BARU (<?php echo $tabel;?>)</h4>
        </div>
        <div class="col-md-8">
            
        </div>    
    </div>
</div>
<div class="row">
    <div class="col-md-12">
            <div class="panel-heading">
                <div class="row">
                    <a href="javascript:void();" class="modalButton btn btn-success" data-toggle="modal" data-src="<?php echo base_url().$url_add;?>" data-target="#modalku">
                        <span class="glyphicon glyphicon-hdd" aria-hidden="true"></span> Upload Data (CSV File)
                    </a>                    
                    <a href="javascript:void();" class="modalButton btn btn-info" data-toggle="modal" data-src="<?php echo base_url();?>index.php/ws_dayatampung/form_createcsv" data-target="#modalku">
                        <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Download Format Data (CSV File)
                    </a>

                    <a href="javascript:void();" class="modalButton btn btn-warning" data-toggle="modal" data-src="<?php echo base_url();?>index.php/welcome/listdir/<?php echo $tabel;?>" data-target="#modalku">
                        <span class="glyphicon glyphicon-tasks" aria-hidden="true"></span> Struktur tabel
                    </a>
                </div>
            </div>
            <table class="table table-hover table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <?php
                            foreach ($listsdic['result'] as $key => $value) {
                                echo "<th>".$value['column_name']."</th>";
                            }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $i=0+$offset;
                        foreach ($listsrec['result'] as $key => $value) {
                            echo "<tr>
                                        <td>".++$i."</td>";
                                        foreach ($listsdic['result'] as $key2 => $value2) {
                                            if (isset($value[$value2['column_name']])) {
                                                if ($value2['column_name']=='id_sms') {
                                                    $temp_isi = ''.get_name_prodi($this->session->userdata('token'), 'sms', 'id_sms=\''.$value[$value2['column_name']].'\' ').' ('.$value[$value2['column_name']].')';
                                                } else {
                                                    $temp_isi = $value[$value2['column_name']];
                                                }
                                            } else {
                                                $temp_isi = "";
                                            }
                                            echo "<td>".$temp_isi."</td>";
                                        }
                               echo "</tr>";
                        }        
                    ?>
                </tbody>
            </table>

            <div class="row">
                <div class="col-md-6" style="margin-top: 40px;">
                    <?php
                        $offset==0? $start=$this->pagination->cur_page: $start=$offset+1;
                        $end = $this->pagination->cur_page * $this->pagination->per_page;
                        
                        //echo "Showing ".$start.' - '.$end.' of '.$total.' result <br />'.$this->pagination->cur_page.'<br />'.$this->pagination->per_page;
                        echo "Showing ".$start.' - '.$end.' of '.$total.' results';
                    ?>
                </div>
                <div class="col-md-6">
                    <?php echo $pagination;?>
                </div>
            </div>
    </div>
</div>