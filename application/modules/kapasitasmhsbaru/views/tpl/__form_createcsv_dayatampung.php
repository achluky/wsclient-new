<?php
    $attributes = array('class' => 'form-signin','enctype' => 'multipart/form-data', 'id' => 'myFRM');
    echo form_open('ws_mahasiswa/dump_csv',$attributes);
?>
<div id="pesan"></div>
<span id="loading"></span>
<h2 class="form-signin-heading">Download Data CSV</h2>
<div class="form-group">
    <label for="prodi">Program Studi</label>
    <select class="form-control" id="prodi" name="prodi">
    <?php
        foreach ($prodi as $key => $value) {
            echo "<option value=\"".$value['id_sms']."\">".$value['nm_lemb']."</option>";
        }
    ?>
    </select>
</div>
<div class="form-group">
    <label for="prodi">Semester</label>
    <select class="form-control" id="smt" name="smt">
    <?php
        foreach ($smt as $key => $value) {
            echo "<option value=\"".$value['id_smt']."\">".$value['nm_smt']."</option>";
        }
    ?>
    </select>
</div>
<div class="form-group">
    <label for="fileinput">Columns separated with:</label>
    <select class="form-control" name="separasi" id="separasi">
        <option value="," selected>Separation with coma (,)</option>
        <option value=";">Separation with semicolon (;)</option>
    </select>
</div>
<button class="btn btn-lg btn-primary btn-block" type="submit" id="btn_upload" class="btn btn-default">Generate</button>
<script>
    $(document).ready(function (e) {
        $("#myFRM").on('submit',(function(e) {
            e.preventDefault();
            $.ajax({
                url: "<?php echo base_url().'index.php/ws_dayatampung/createcsv'; ?>",
                type: "POST",
                data: new FormData(this),
                mimeType:"multipart/form-data",
                contentType: false,
                cache: false,
                processData:false,
                beforeSend:function()
                {
                    $("#pesan").hide();
                    $("#loading").html('<i class=\"fa fa-spinner fa-spin\"></i> Generate files processing...Please wait...');
                },
                complete:function()
                {
                    $("#loading").empty();
                    $("#pesan").show();
                },
                error: function()
                {
                    $('#pesan').html('Error, unknown');
                },
                success: function(data)
                {
                    $("#pesan").html(data);
                }
            });
        }));
    });
</script>
<?php echo form_close();?>