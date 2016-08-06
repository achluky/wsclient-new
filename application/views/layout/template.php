<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset ($site_title)?$site_title.' | '.$this->config->item('site_title'):$this->config->item('site_title'); ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="<?php echo $this->config->item('meta_desc');?>" name="description" />
    <meta content="<?php echo $this->config->item('meta_key');?>" name="keywords" />
    <meta content="<?php echo $this->config->item('meta_author');?>" name="author" />
    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url();?>assets/css/bootstrap.min.css?v=3.3.5" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="<?php echo base_url();?>assets/font-awesome/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="<?php echo base_url();?>assets/css/bootstrap-switch.min.css?v=3.3.2" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/dataTables.bootstrap.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/ladda-themeless.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/dropdowns-enhancement.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/select2.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/select2-bootstrap.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/app.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
  <?php
      $temp_pecah = explode('/ws/', $this->session->userdata('ws'));
      $temp_pecah2 = explode('.php?wsdl', $temp_pecah[1]);
  ?>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top <?php echo $temp_pecah2['0']=='live'?"":"navbar-inverse" ?>">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo base_url();?>"><i class="glyphicon glyphicon-sunglasses"></i> WS Feeder DIKTI Client - ITERA</a>
        </div>
    
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <?php
                if ($this->session->userdata('login')) {
                    echo "<ul class=\"nav navbar-nav\">
                                <li class=\"dropdown\">
                                  <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\"><i class=\"fa fa-clone\"></i> Import Data <span class=\"caret\"></span></a>
                                  <ul class=\"dropdown-menu\" role=\"menu\">
                                    <li><a href=\"".base_url()."mahasiswa\"><i class=\"fa fa-graduation-cap\"></i> Mahasiswa</a></li>
                                    <li class=\"divider\"></li>
                                    <li><a href=\"".base_url()."akm\"><i class=\"fa fa-graduation-cap\"></i> Aktivitas Kuliah Mahasiswa</a></li>
                                    <li><a href=\"".base_url()."matakuliah\"><i class=\"fa fa-graduation-cap\"></i> Mata Kuliah</a></li>
                                    <li><a href=\"".base_url()."substansikuliah\"><i class=\"fa fa-graduation-cap\"></i> Substansi Kuliah</a></li>
                                    <li><a href=\"".base_url()."kelas\"><i class=\"fa fa-graduation-cap\"></i> Kelas/Nilai Perkuliahan</a></li>
                                    <li class=\"divider\"></li>
                                    <li><a href=\"".base_url()."skalanilai\"><i class=\"fa fa-graduation-cap\"></i> Skala Nilai</a></li>
                                    <li><a href=\"".base_url()."kapasitasmhsbaru\"><i class=\"fa fa-graduation-cap\"></i> Kapasitas Mahasiswa Baru</a></li>
                                    
                                  </ul> 
                                </li>
                                <li class=\"dropdown\">
                                  <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\"><i class=\"fa fa-th\"></i> Data Referensi <span class=\"caret\"></span></a>
                                  <ul class=\"dropdown-menu\" role=\"menu\">
                                    <li><a href=\"".base_url()."ref_agama\">Data Agama</a></li>
                                    <li><a href=\"".base_url()."kk\">Data Kebutuhan Khusus</a></li>
                                    <li><a href=\"".base_url()."wilayah\">Data Wilayah</a></li>
                                  </ul>
                                </li>
                                <li class=\"dropdown\">
                                  <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\"><i class=\"fa fa-download\"></i> Eksport Data <span class=\"caret\"></span></a>
                                  <ul class=\"dropdown-menu\" role=\"menu\">
                                    <li><a href=\"".base_url()."krskhs/krs\">KRS Mahasiswa</a></li>
                                    <li><a href=\"".base_url()."krskhs/khs\">KHS Mahasiswa</a></li>
                                  </ul>
                                </li>
                                <li><a href=\"".base_url()."welcome/table\"><i class=\"fa fa-database\"></i> List Tabel</a></li>
                      </ul>";
                }
            ?>
          
          <ul class="nav navbar-nav navbar-right">
            <?php
                echo "    <li class=\"dropdown active\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\">
                                    <i class=\"fa fa-user\"></i>  ".$this->session->userdata('username')." on ".$this->session->userdata('nm_lemb')." <span class=\"caret\"></span>
                              </a>
                              <ul class=\"dropdown-menu\" role=\"menu\">
                                <!--li><a href=\"".base_url()."welcome/loginas\"><i class=\"fa fa-users\"></i> Login as</a></li>
                                <li class=\"divider\"></li-->
                                <li><a href=\"".base_url()."welcome/token/".$this->uri->segment(1)."-".$this->uri->segment(2)."\"><i class=\"fa fa-random\"></i>  Generate Token</a></li>
                                <li><a href=\"".base_url()."welcome/setting\"><i class=\"fa fa-cog\"></i> Setting</a></li>
                                <li class=\"divider\"></li>
                                <li><a href=\"".base_url()."welcome/logout\"><i class=\"fa fa-sign-out\"></i> Logout</a></li>
                              </ul>
                          </li>";
            ?>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>

    <div class="container-fluid ws-container">
        <?php echo $view; ?>
    </div> <!-- /container -->

    <footer class="footer">
      <div class="container copy">
        ITERA &copy; <?php echo date('Y');?> <a href="https://github.com/achluky/wsclient-new" target="_blank_">GitHub</a>
      </div>
    </footer>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script>var top_url = '<?php echo base_url();?>'; </script>
    <script src="<?php echo base_url();?>assets/js/jquery.js?v=2.1.3"></script>
    <script src="<?php echo base_url();?>assets/js/bootstrap.min.js?v=3.3.4"></script>
    <script src="<?php echo base_url();?>assets/js/bootstrap-switch.min.js?v=3.3.2"></script>
    <script src="<?php echo base_url(); ?>assets/js/jquery.dataTables.min.js?v=1.10.8"></script>
    <script src="<?php echo base_url(); ?>assets/js/dataTables.bootstrap.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/select2.full.min.js?v=3.5.4"></script>
    <script src="<?php echo base_url(); ?>assets/js/spin.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/ladda.min.js?v=0.9.4"></script>
    <script src="<?php echo base_url(); ?>assets/js/dropdowns-enhancement.js?v=3.1.1"></script>
    <script src="<?php echo base_url();?>assets/js/back-to-top.js"></script>
    <script src="<?php echo base_url();?>assets/js/jquery.form.js"></script>
    <script src="<?php echo base_url();?>assets/js/app.js"></script>
    <?php
        if ($assign_js != '') {
            $this->load->view($assign_js);
        }

        if ($assign_modal != '') {
            $this->load->view($assign_modal);
        }
    ?>
  </body>
</html>
