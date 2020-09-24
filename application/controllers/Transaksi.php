<?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    ini_set('memory_limit','4096M'); 
    require('./application/third_party/phpoffice/vendor/autoload.php');

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    
    class Transaksi extends CI_Controller {
        
        function __construct()
        {
            parent::__construct();

            $this->load->model('Model_transaksi');
            $this->load->model('Model_group_access');
            $this->load->library('pdf');
            $this->load->library('csvimport');
        }
        
        function index(){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
                        
            $session_data = $this->session->userdata('logged_in');            
            $result['nama_pengguna'] = $session_data['nama_pengguna'];
            $result['username'] = $session_data['username'];
            $result['group_pengguna'] = $session_data['group_pengguna'];
            $result['provinsi_pengguna'] = $session_data['provinsi'];
            $result['kabupaten_pengguna'] = $session_data['kabupaten'];
            
            $result['menu_group_none'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'')->result();
            $result['menu_group_transaksi'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Transaksi')->result();
            $result['menu_group_laporan'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Laporan')->result();
            
            $result['provinsi'] = $this->Model_transaksi->getProvinsi($session_data['provinsi'])->result();
            $result['menu'] = $result['menu_group_transaksi'][0]->module_name;
            
            $this->load->view('transaksi/data_ganda', $result);
        }
        
        function ganda_identik(){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
                        
            $session_data = $this->session->userdata('logged_in');            
            $result['nama_pengguna'] = $session_data['nama_pengguna'];
            $result['username'] = $session_data['username'];
            $result['group_pengguna'] = $session_data['group_pengguna'];
            $result['provinsi_pengguna'] = $session_data['provinsi'];
            $result['kabupaten_pengguna'] = $session_data['kabupaten'];
            
            $result['menu_group_none'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'')->result();
            $result['menu_group_transaksi'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Transaksi')->result();
            $result['menu_group_laporan'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Laporan')->result();
            
            $result['provinsi'] = $this->Model_transaksi->getProvinsi($session_data['provinsi'])->result();
            $result['menu'] = $result['menu_group_transaksi'][0]->module_name;
            
            $this->load->view('transaksi/data_ganda_identik', $result);
        }
        
        function list_of_kab(){
            $postData = $this->input->post();
            $session_data = $this->session->userdata('logged_in');

            $prov = isset($postData['prov'])?$postData['prov']:'';
            $kab = $session_data['kabupaten'];
            $result['kab_kota'] = $this->Model_transaksi->getKabKota($prov,$kab)->result();
            $this->load->view('transaksi/v_kab_list', $result);
        }
        
        function list_of_kec(){
            $postData = $this->input->post();

            $kab = isset($postData['kab'])?$postData['kab']:'';
            $result['kecamatan'] = $this->Model_transaksi->getKecamatan($kab)->result();
            $this->load->view('transaksi/v_kec_list', $result);
        }
        
        function list_of_kel(){
            $postData = $this->input->post();

            $kec = isset($postData['kec'])?$postData['kec']:'';
            $result['kelurahan'] = $this->Model_transaksi->getKelDesa($kec)->result();
            $this->load->view('transaksi/v_kel_list', $result);
        }
        
        function update_status(){
            $postData = $this->input->post();
            
            $this->Model_transaksi->update_status($postData['id'],$postData['status']);
            $hasil = $this->Model_transaksi->rekap_data_prop_kab($postData['prop'],$postData['kab'])->result();
            $jmlClean = $hasil[0]->jml_clean;
            $jmlUnClean = $hasil[0]->jml_unclean;
            $jmlNonAktif = $hasil[0]->jml_nonaktif;
            echo "Data berhasil di update~Propinsi: ".$postData['prop']."\nKabupate/Kota: ".$postData['kab']."\nClean: ".$jmlClean."\nUnclean: ".$jmlUnClean."\nNon Aktif: ".$jmlNonAktif;
        }
                        
        function rubah_data_ganda($id){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
            
            $session_data = $this->session->userdata('logged_in');            
            $data['nama_pengguna'] = $session_data['nama_pengguna'];
            $data['username'] = $session_data['username'];
            $data['group_pengguna'] = $session_data['group_pengguna'];
            $data['provinsi_pengguna'] = $session_data['provinsi'];
            $result['kabupaten_pengguna'] = $session_data['kabupaten'];
            
            $data['menu_group_none'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'')->result();
            $data['menu_group_transaksi'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Transaksi')->result();
            $data['menu_group_laporan'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Laporan')->result();
            
            $prefixTbl = $this->db->dbprefix;
            $data['menu'] = $data['menu_group_transaksi'][0]->module_name;
            $where = array('IDARTBDT' => $id);
            $data['user'] = $this->Model_transaksi->edit_data($where,$prefixTbl.'data_ganda')->result();
            $data['provinsi'] = $this->Model_transaksi->getProvinsi($session_data['provinsi'])->result();            
            $prov = $data['user'][0]->NMPROP;
            $data['kab_kota'] = $this->Model_transaksi->getKabKota($prov,$session_data['kabupaten'])->result();
            $kab = $data['user'][0]->NMKAB;
            $data['kecamatan'] = $this->Model_transaksi->getKecamatan($kab)->result();
            $kec = $data['user'][0]->NMKEC;
            $data['kelurahan'] = $this->Model_transaksi->getKelDesa($kec)->result();
            
            $this->load->view('transaksi/edit_data',$data);
	}
        
        function list_data(){
            $postData = $this->input->post();

            $prov = isset($postData['prov'])?$postData['prov']:'';
            $kab = isset($postData['kab'])?$postData['kab']:'';
            $kec = isset($postData['kec'])?$postData['kec']:'';
            $kel = isset($postData['kel'])?$postData['kel']:'';
            $ket_tambahan = isset($postData['ket'])?$postData['ket']:'';
            $nik = isset($postData['nik'])?$postData['nik']:'';
            $nama = isset($postData['nama'])?$postData['nama']:'';

            $result['data'] = $this->Model_transaksi->showData($prov,$kab,$kec,$kel,$ket_tambahan,$nik,$nama)->result();      
            $this->load->view('transaksi/data_ganda_list', $result);
        }
        
        function list_data_identik(){
            $postData = $this->input->post();

            $prov = isset($postData['prov'])?$postData['prov']:'';
            $kab = isset($postData['kab'])?$postData['kab']:'';
            $kec = isset($postData['kec'])?$postData['kec']:'';
            $kel = isset($postData['kel'])?$postData['kel']:'';
            $ket_tambahan = isset($postData['ket'])?$postData['ket']:'';
            $nik = isset($postData['nik'])?$postData['nik']:'';
            $nama = isset($postData['nama'])?$postData['nama']:'';

            $result['data'] = $this->Model_transaksi->showData($prov,$kab,$kec,$kel,$ket_tambahan,$nik,$nama)->result();      
            $this->load->view('transaksi/data_ganda_list_identik', $result);
        }
        
        function update(){
            $nama_penerima = $this->input->post('nm_penerima');
            $no_kartu = $this->input->post('no_kartu');
            $nik_ktp = $this->input->post('nik_ktp');
            $id_pengurus = $this->input->post('id_pengurus');
            $alamat = $this->input->post('alamat');
            $provinsi = $this->input->post('provinsi');
            $kab_kota = $this->input->post('kab_kota');
            $kecamatan = $this->input->post('kecamatan');
            $kel_desa = $this->input->post('kel_desa');
            $idbdt = $this->input->post('idbdt');
            $idartbdt = $this->input->post('idartbdt');
            $nmdtks = $this->input->post('nmdtks');
            $idkeluarga = $this->input->post('idkeluarga');
            $ket_tambahan = $this->input->post('ket_tambahan');

            $data = array(
                'NAMA_PENERIMA' => $nama_penerima,
                'NOMOR_KARTU' => $no_kartu,
                'NIK_KTP' => $nik_ktp,
                'ID_PENGURUS' => $id_pengurus,
                'NMPROP' => $provinsi,
                'NMKAB' => $kab_kota,
                'NMKEC' => $kecamatan,
                'NMKEL' => $kel_desa,
                'ALAMAT' => $alamat,
                'IDBDT' => $idbdt,
                'IDARTBDT' => $idartbdt,
                'IDPENGURUS' => $id_pengurus,
                'NAMA_DTKS' => $nmdtks,
                'IDKELUARGA' => $idkeluarga,
                'KET_TAMBAHAN' => $ket_tambahan
            );

            $where = array(
                'IDARTBDT' => $idartbdt
            );

            $prefixTbl = $this->db->dbprefix;
            $this->Model_transaksi->update_data($where,$data,$prefixTbl.'data_ganda');
            redirect('transaksi/index');
        }
        
        function export_pdf(){
            $postData = $this->input->post();

            $prov = isset($postData['provinsi'])?$postData['provinsi']:'';
            $kab = isset($postData['kab_kotax'])?$postData['kab_kotax']:'';
            $kec = isset($postData['kecamatanx'])?$postData['kecamatanx']:'';
            $kel = isset($postData['kel_desax'])?$postData['kel_desax']:'';
            $ket_tambahan = isset($postData['ket_tambahan'])?$postData['ket_tambahan']:'';
            $nik = isset($postData['nik'])?$postData['nik']:'';
            $nama = isset($postData['nama'])?$postData['nama']:'';
            
            $pdf = new FPDF('L','mm','Legal');
            // membuat halaman baru
            $pdf->AddPage();
            $pdf->SetLeftMargin(5);       
            $pdf->SetAutoPageBreak(20, 4);
            // setting jenis font yang akan digunakan
            $pdf->SetFont('Arial','B',16);
            // mencetak string 
            $pdf->Cell(330,7,'DATA GANDA PENERIMA BANTUAN (BSP)',0,1,'C');         
            // Memberikan space kebawah agar tidak terlalu rapat
            $pdf->Cell(10,7,'',0,1);            
            
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(35,8,'NO KARTU',1,0);
            $pdf->Cell(35,8,'NIK KTP',1,0);
            $pdf->Cell(63,8,'NAMA PENERIMA',1,0);            
            $pdf->Cell(45,8,'PROPINSI',1,0);
            $pdf->Cell(45,8,'KABUPATEN',1,0);
            $pdf->Cell(45,8,'KECAMATAN',1,0);
            $pdf->Cell(50,8,'KELURAHAN',1,0);
            $pdf->Cell(27,8,'KETERANGAN',1,1);
            $pdf->SetFont('Arial','',10);
           
            $result = $this->Model_transaksi->showData($prov,$kab,$kec,$kel,$ket_tambahan,$nik,$nama)->result(); 
            foreach ($result as $row){
                $pdf->Cell(35,7,$row->NOMOR_KARTU,1,0);
                $pdf->Cell(35,7,$row->NIK_KTP,1,0);
                $pdf->Cell(63,7,$row->NAMA_PENERIMA,1,0);  
                $pdf->Cell(45,7,$row->NMPROP,1,0); 
                $pdf->Cell(45,7,$row->NMKAB,1,0); 
                $pdf->Cell(45,7,$row->NMKEC,1,0); 
                $pdf->Cell(50,7,$row->NMKEL,1,0); 
                $pdf->Cell(27,7,$row->KET_TAMBAHAN,1,1); 
            }
            $pdf->Output();
        }
        
        function surat_permohonan(){
            $data['menu'] = "surat_permohonan";
            $data['provinsi'] = $this->Model_transaksi->getProvinsi()->result();
            $this->load->view('transaksi/template_surat_permohonan',$data);
        }
        
        function submit_surat_permohonan(){
            $tgl_permohonan = $this->input->post('tgl_permohonan');
            $nama_pemohon = $this->input->post('nama_pemohon');
            $nip_pemohon = $this->input->post('nip_pemohon');
            $provinsi = $this->input->post('provinsi');
            $kab_kota = $this->input->post('kab_kota');
            $kecamatan = $this->input->post('kecamatan');
            $kel_desa = $this->input->post('kel_desa');
            $jml_clean = $this->input->post('jml_clean');
            $jml_unclean = $this->input->post('jml_unclean');
            $jml_nonaktif = $this->input->post('jml_nonaktif');
            
            $data = array(
                'tgl_permohonan' => $tgl_permohonan,
                'nama_pemohon' => $nama_pemohon,
                'nip_pemohon' => $nip_pemohon,
                'nm_provinsi' => $provinsi,
                'nm_kabupaten' => $kab_kota,
                'nm_kecamatan' => $kecamatan,
                'nm_kelurahan' => $kel_desa,
                'jml_clean' => $jml_clean,
                'jml_unclean' => $jml_unclean,
                'jml_nonaktif' => $jml_nonaktif,
            );

            $insertID = $this->Model_transaksi->submit_surat_permohonan($data);
            
            $this->view_surat_permohonan($insertID);
        }
        
        function view_surat_permohonan($idData){            
            $this->load->view('transaksi/surat_permohonan_view',$idData);
        }
        
        function download_surat(){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
            
            $session_data = $this->session->userdata('logged_in');            
            $data['nama_pengguna'] = $session_data['nama_pengguna'];
            $data['username'] = $session_data['username'];
            $data['group_pengguna'] = $session_data['group_pengguna'];
            $data['provinsi_pengguna'] = $session_data['provinsi'];
            $data['kabupaten_pengguna'] = $session_data['kabupaten'];
            
            $data['menu_group_none'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'')->result();
            $data['menu_group_transaksi'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Transaksi')->result();
            $data['menu_group_laporan'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Laporan')->result();            
            
            $data['menu'] = "surat_permohonan";
            $this->load->view('transaksi/surat_permohonan_view', $data);
        }
        
        function upload_surat(){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
            
            $session_data = $this->session->userdata('logged_in');            
            $data['nama_pengguna'] = $session_data['nama_pengguna'];
            $data['username'] = $session_data['username'];
            $data['group_pengguna'] = $session_data['group_pengguna'];
            $data['provinsi_pengguna'] = $session_data['provinsi'];
            $data['kabupaten_pengguna'] = $session_data['kabupaten'];
            
            $data['menu_group_none'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'')->result();
            $data['menu_group_transaksi'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Transaksi')->result();
            $data['menu_group_laporan'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Laporan')->result();            
            
            $data['menu'] = "surat_permohonan";
            $data['provinsi'] = $this->Model_transaksi->getProvinsi($session_data['provinsi'])->result();
            $data['noregister'] = $this->Model_transaksi->getNoRegDataRevisi($session_data['provinsi'],$session_data['kabupaten'])->result();
            $this->load->view('transaksi/surat_permohonan_upload', $data);
        }
        
        function submit_upload_surat(){
            $session_data = $this->session->userdata('logged_in');            
            $nama_pengguna = $session_data['nama_pengguna'];
            $tgl_permohonan = date("Y-m-d");
            $provinsi = $this->input->post('provinsi');
            $kab_kota = $this->input->post('kab_kotax');
            $no_registrasi_revisi = $this->input->post('noregister');
            $nm_surat_permohonan = $this->input->post('surat_permohonan');
            $nm_lampiran_dokumen = $this->input->post('lampiran_dokumen');
            $dateUpload = date("dmy");
            
            $this->load->library('upload');
            
            $fileSurat = $dateUpload."_Surat_Permohonan_".$provinsi."_".$kab_kota.".pdf";
            $config['upload_path'] = './uploads/';
            $config['allowed_types'] = 'pdf';
            $config['file_name'] = $fileSurat;
            
            $this->upload->initialize($config);
            $this->upload->do_upload('surat_permohonan');
            $result1 = $this->upload->data();
            
            $fileLampiran = $dateUpload."_Lampiran_Data_".$provinsi."_".$kab_kota.".pdf";
            $config2['upload_path'] = './uploads/';
            $config2['allowed_types'] = 'pdf';
            $config2['file_name'] = $fileLampiran;
            
            $this->upload->initialize($config2);        
            $this->upload->do_upload('lampiran_dokumen');
            $result2 = $this->upload->data();
                        
            $result = array('surat_permohonan'=>$result1,'lampiran_dokumen'=>$result2);
            
            $data = array(
                'tgl_permohonan' => $tgl_permohonan,
                'nm_pemohon' => $nama_pengguna,
                'nm_propinsi' => $provinsi,
                'nm_kabupaten' => $kab_kota,
                'nm_surat_permohonan' => $result['surat_permohonan']['file_name'],
                'nm_lampiran_dokumen' => $result['lampiran_dokumen']['file_name'],
                'status_permohonan' => 'Open',
                'nm_pengecek' => '',
                'alasan_tolak' => '',
                'tgl_acc_dinsos' => '',
                'acc_dinsos_by' => '',
                'tgl_acc_provinsi' => '',
                'acc_provinsi_by' => '',
                'tgl_acc_pfm' => '',
                'acc_pfm_by' => '',
                'tgl_update_revisi' => '',
                'update_revisi_by' => '',
                'no_registrasi_revisi' => $no_registrasi_revisi
            );
        
            $insert = $this->Model_transaksi->submit_unggah_surat($data);
            if($insert > 0){
                $this->session->set_flashdata('pesan', 'Dokumen Berhasil Terkirim');
            } else {
                $this->session->set_flashdata('pesan', 'Dokumen Tidak Berhasil Terkirim');
            }
            redirect('transaksi/upload_surat');
        }
        
        function daftar_surat(){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
            
            $session_data = $this->session->userdata('logged_in');            
            $data['nama_pengguna'] = $session_data['nama_pengguna'];
            $data['username'] = $session_data['username'];
            $data['group_pengguna'] = $session_data['group_pengguna'];
            $data['provinsi_pengguna'] = $session_data['provinsi'];
            $data['kabupaten_pengguna'] = $session_data['kabupaten'];
            
            $data['menu_group_none'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'')->result();
            $data['menu_group_transaksi'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Transaksi')->result();
            $data['menu_group_laporan'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Laporan')->result();
            
            $data['menu'] = "surat_permohonan";
            $data['provinsi'] = $this->Model_transaksi->getProvinsi($session_data['provinsi'])->result();
            $this->load->view('transaksi/surat_permohonan_list', $data);
        }
        
        function daftar_surat_list(){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
            
            $session_data = $this->session->userdata('logged_in');
            $data['group_pengguna'] = $session_data['group_pengguna'];
            
            if($session_data['group_pengguna']=="Dinsos"){
                $statusDoc = "'Open'";
            } elseif($session_data['group_pengguna']=="Provinsi"){
                $statusDoc = "'Need Validation'";
            } elseif($session_data['group_pengguna']=="PFM"){
                $statusDoc = "'Need Approve'";
            } elseif($session_data['group_pengguna']=="Korda"){
                $statusDoc = "'Open','Need Validation','Need Approve','Rejected','Approved'";
            }
            
            $provinsi = $this->input->post('prov');
            $kab_kota = $this->input->post('kab');
            $data['list_data'] = $this->Model_transaksi->show_list_surat_permohonan($provinsi,$kab_kota,$statusDoc)->result();
            $this->load->view('transaksi/surat_permohonan_list_daftar', $data);
        }
        
        function approve_surat_permohonan($idsurat,$status){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
            
            $session_data = $this->session->userdata('logged_in');
            
            if($status == "NV"){
                $statusname = "Need Validation";
            } elseif($status == "NA"){
                $statusname = "Need Approve";
            } elseif($status == "A"){
                $statusname = "Approved";
            } elseif($status == "RJ"){
                $statusname = "Rejected";
            }
            $tglAcc = date("Y-m-d");
            if($session_data['group_pengguna']=="Dinsos"){
                $fieldTglAcc = "tgl_acc_dinsos";
                $fieldAccBy = "acc_dinsos_by";
            } elseif($session_data['group_pengguna']=="Provinsi"){
                $fieldTglAcc = "tgl_acc_provinsi";
                $fieldAccBy = "acc_provinsi_by";
            } elseif($session_data['group_pengguna']=="PFM"){
                $fieldTglAcc = "tgl_acc_pfm";
                $fieldAccBy = "acc_pfm_by";
            }
            $data_update = array("status_permohonan" => $statusname,$fieldTglAcc => $tglAcc,$fieldAccBy => $session_data['nama_pengguna']);
            $where = array("id" => $idsurat);
            $this->Model_transaksi->update_data($where,$data_update,$prefixTbl.'surat_permohonan_data_ganda');
            redirect('transaksi/daftar_surat');
        }
        
        function rubah_status_permohonan($id_surat){
            $session_data = $this->session->userdata('logged_in');
            $data['nama_pengguna'] = $session_data['nama_pengguna'];
            $prefixTbl = $this->db->dbprefix;
            $where = array('id' => $id_surat);
            $data['data_surat'] = $this->Model_transaksi->showPermohonanById($where,$prefixTbl."surat_permohonan_data_ganda")->result();
            $data['provinsi'] = $this->Model_transaksi->getProvinsi($session_data['provinsi'])->result();            
            $prov = $data['data_surat'][0]->nm_propinsi;
            $nm_kab = $data['data_surat'][0]->nm_kabupaten;
            $data['kab_kota'] = $this->Model_transaksi->getKabKota($prov,$nm_kab)->result();
            $kab = $data['data_surat'][0]->nm_kabupaten;
            $this->load->view('transaksi/rubah_status_surat_permohonan_form', $data);
        }
        
        function update_status_surat(){
            $id_surat = $this->input->post('id_surat');
            $status_opt = $this->input->post('status_surat');
            $status_surat = "Rejected";
            $alasan_tolak = $this->input->post('alasan_tolak');
            $nm_pengcek = $this->input->post('nm_pengecek');
            
            $data = array(
                'nm_pengecek' => $nm_pengcek,
                'alasan_tolak' => $alasan_tolak,
                'status_permohonan' => $status_surat,
                'tgl_rejected' => date("Y-m-d")
            );

            $where = array(
                'id' => $id_surat
            );

            $prefixTbl = $this->db->dbprefix;
            $this->Model_transaksi->update_data($where,$data,$prefixTbl.'surat_permohonan_data_ganda');
            redirect('transaksi/daftar_surat');
        }
        
        function cek_data_permohonan($id_surat,$id_upload,$ket){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
            
            $session_data = $this->session->userdata('logged_in');            
            $data['nama_pengguna'] = $session_data['nama_pengguna'];
            $data['username'] = $session_data['username'];
            $data['group_pengguna'] = $session_data['group_pengguna'];
            $data['provinsi_pengguna'] = $session_data['provinsi'];
            $data['kabupaten_pengguna'] = $session_data['kabupaten'];
            
            $data['menu_group_none'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'')->result();
            $data['menu_group_transaksi'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Transaksi')->result();
            $data['menu_group_laporan'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Laporan')->result();
            
            $data['menu'] = "surat_permohonan";
            
            $prefixTbl = $this->db->dbprefix;
            
            $where = array(
                        'id' => $id_surat
                    );
            $data_surat = $this->Model_transaksi->showPermohonanById($where,$prefixTbl."surat_permohonan_data_ganda")->result();
            $nmProp = $data_surat[0]->nm_propinsi;
            $nmKab = $data_surat[0]->nm_kabupaten;
            $ketTambahan = ($ket != "0") ? $ket : "";
            
            if($ketTambahan != ""){
                $where2 = array (
                            'ID_UPLOAD' => $id_upload,
                            'STATUS_BARU' => $ket
                        );    
            } else {
                $where2 = array (
                            'ID_UPLOAD' => $id_upload
                        );
            }
            
            //$where2 = array('ID_UPLOAD' => $id_upload);
            $data['data_surat'] = $this->Model_transaksi->showPermohonanById($where2,$prefixTbl."data_ganda_revisi")->result();
            $data['id_surat'] = $id_surat;
            $data['id_upload'] = $id_upload;
            $data['nm_propinsi'] = $nmProp;
            $data['nm_kabupaten'] = $nmKab;
            $data['keterangan'] = $ketTambahan;
            $this->load->view('transaksi/list_cek_data_permohonan', $data);
        }
        
        function download_data($idSurat,$keterangan){ 
            $where = array(
                        'id' => $idSurat
                    );
            $prefixTbl = $this->db->dbprefix;
            $data_surat = $this->Model_transaksi->showPermohonanById($where,$prefixTbl."surat_permohonan_data_ganda")->result();
            $nmProp = $data_surat[0]->nm_propinsi;
            $nmKab = $data_surat[0]->nm_kabupaten;            
            $where2 = array (
                        'NMPROP' => $nmProp,
                        'NMKAB' => $nmKab,
                        'KET_TAMBAHAN' => $keterangan
                    );
            $result = $this->Model_transaksi->showPermohonanById($where2,"data_ganda")->result(); 
            $ketStatus = $result[0]->KET_TAMBAHAN;
            
            $pdf = new FPDF('L','mm','Legal');
            // membuat halaman baru
            $pdf->AddPage();
            $pdf->SetLeftMargin(5);       
            $pdf->SetAutoPageBreak(20, 4);
            // setting jenis font yang akan digunakan
            $pdf->SetFont('Courier','B',16);
            // mencetak string 
            $pdf->Cell(330,7,'VALIDASI DATA GANDA PENERIMA BANTUAN (BSP)',0,1,'C');         
            $pdf->Cell(10,7,'',0,1);
            $pdf->SetFont('Courier','B',12);
            $pdf->Cell(110,7,'PROPINSI: '.$nmProp,0,0,'C');         
            $pdf->Cell(110,7,'KABUPATEN: '.$nmKab,0,0,'C');         
            $pdf->Cell(110,7,'KET STATUS: '.$ketStatus,0,0,'C');         
            // Memberikan space kebawah agar tidak terlalu rapat
            $pdf->Cell(10,7,'',0,1);            
            
            $pdf->SetFont('Helvetica','B',11);
            $pdf->Cell(36,8,'NO KARTU',1,0,'C');
            $pdf->Cell(36,8,'NIK KTP',1,0,'C');
            $pdf->Cell(36,8,'NO KK',1,0,'C');
            $pdf->Cell(40,8,'IDARTBDT',1,0,'C');
            $pdf->Cell(83,8,'NAMA PENERIMA',1,0,'C');            
            $pdf->Cell(55,8,'KECAMATAN',1,0,'C');
            $pdf->Cell(55,8,'KELURAHAN',1,1,'C');
            
            $pdf->SetFont('Helvetica','',10);                                   
            foreach ($result as $row){
                $pdf->Cell(36,7,$row->NOMOR_KARTU,1,0,'C');
                $pdf->Cell(36,7,$row->NIK_KTP,1,0,'C');
                $pdf->Cell(36,7,$row->NOKK_DTKS,1,0,'C');
                $pdf->Cell(40,7,$row->IDARTBDT,1,0,'C');
                $pdf->Cell(83,7,$row->NAMA_PENERIMA,1,0,'L');  
                $pdf->Cell(55,7,$row->NMKEC,1,0,'C'); 
                $pdf->Cell(55,7,$row->NMKEL,1,1,'C'); 
            }
            $pdf->Output();
        }                
        
        function exportCSV($prov,$kab,$kec,$kel,$ket_tambahan,$nik,$nama){
            //get parameter            
            $prov = ($prov!="")?str_replace("%20", " ", $prov):"";
            $kab = ($kab!="0")?str_replace("%20", " ", $kab):"";
            $kec = ($kec!="0")?str_replace("%20", " ", $kec):"";
            $kel = ($kel!="0")?str_replace("%20", " ", $kel):"";
            $ket_tambahan = ($ket_tambahan!="0")?$ket_tambahan:"";
            $nik = ($nik!="0")?$nik:"";
            $nama = ($nama!="0")?$nama:"";

            $provName = str_replace(" ", "_", $prov);
            $kabName = ($kab!="0") ? str_replace(" ", "_", $kab) : "All";
            
            // get data
            $myData = $this->Model_transaksi->showData($prov,$kab,$kec,$kel,$ket_tambahan,$nik,$nama)->result();

            // file name
            $filename = 'Data_Ganda_Penerima_Bantuan_'.$provName.'_'.$kabName.'.csv';
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$filename");
            header("Content-Type: application/csv; ");

            $delimiter = ",";
            // file creation
            $file = fopen('php://output', 'w');

            $header = array("NAMA_PENERIMA","NOMOR_KARTU","NIK_KTP","KODE_WILAYAH","FLAG","STATUS_REKENING","STATUS_KARTU","STATUS_NIK",
                "NAMA_REKENING_DI_BANK","NO_KARTU_DI_BANK","NO_NIK","WIL","BANK","ID_PENGURUS","NMPROP","NMKAB","NMKEC","NMKEL","ALAMAT",
                "KET","KETDATA","NOPESERTAPKH","KDPROP","IDBDT","IDARTBDT","IDPENGURUS","NAMA_DTKS","NIK_DTKS","NOKK_DTKS","TGLLAHIR_DTKS",
                "NAMAIBU_DTKS","FLAGNIK","PERCENTILE","STADATA","STAGANDA","IDKELUARGA","KET_TAMBAHAN","NOREKENING");
            fputcsv($file, $header, $delimiter);

            foreach ($myData as $line){
                fputcsv
                (
                    $file,array
                    (
                        $line -> NAMA_PENERIMA,
                        $line -> NOMOR_KARTU,
                        $line -> NIK_KTP,
                        $line -> KODE_WILAYAH,
                        $line -> FLAG,
                        $line -> STATUS_REKENING,
                        $line -> STATUS_KARTU,
                        $line -> STATUS_NIK,
                        $line -> NAMA_REKENING_DI_BANK,
                        $line -> NO_KARTU_DI_BANK,
                        $line -> NO_NIK,
                        $line -> WIL,
                        $line -> BANK,
                        $line -> ID_PENGURUS,
                        $line -> NMPROP,
                        $line -> NMKAB,
                        $line -> NMKEC,
                        $line -> NMKEL,
                        $line -> ALAMAT,
                        $line -> KET,
                        $line -> KETDATA,
                        $line -> NOPESERTAPKH,
                        $line -> KDPROP,
                        $line -> IDBDT,
                        $line -> IDARTBDT,
                        $line -> IDPENGURUS,
                        $line -> NAMA_DTKS,
                        $line -> NIK_DTKS,
                        $line -> NOKK_DTKS,
                        $line -> TGLLAHIR_DTKS,
                        $line -> NAMAIBU_DTKS,
                        $line -> FLAGNIK,
                        $line -> PERCENTILE,
                        $line -> STADATA,
                        $line -> STAGANDA,
                        $line -> IDKELUARGA,
                        $line -> KET_TAMBAHAN,
                        $line -> NOREKENING
                    ),$delimiter
                );
            }

            fclose($file);
            exit;
        }                
        
        function exportExcel($prov,$kab,$kec,$kel,$ket_tambahan,$nik,$nama,$idupload="",$type="A"){
            //get parameter            
            $prov = ($prov!="")?str_replace("%20", " ", $prov):"";
            $kab = ($kab!="0")?str_replace("%20", " ", $kab):"";
            $kec = ($kec!="0")?str_replace("%20", " ", $kec):"";
            $kel = ($kel!="0")?str_replace("%20", " ", $kel):"";
            $ket_tambahan = ($ket_tambahan!="0")?$ket_tambahan:"";
            $nik = ($nik!="0")?$nik:"";
            $nama = ($nama!="0")?$nama:"";

            $provName = str_replace(" ", "_", $prov);
            $kabName = ($kab!="0") ? str_replace(" ", "_", $kab) : "All";            
            
            // Load plugin PHPExcel nya
            include APPPATH.'third_party/PHPExcel/PHPExcel.php';

            // Panggil class PHPExcel nya
            $excel = new PHPExcel();

            // Settingan awal fil excel
            $excel->getProperties()->setCreator('My Notes Code')
            ->setLastModifiedBy('My Notes Code')
            ->setTitle("Data Siswa")
            ->setSubject("Siswa")
            ->setDescription("Laporan Semua Data Siswa")
            ->setKeywords("Data Siswa");

            // Buat sebuah variabel untuk menampung pengaturan style dari header tabel
            $style_col = array(
                'font' => array('bold' => true), // Set font nya jadi bold
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
                ),
                'borders' => array(
                'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
                'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
                'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
                'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
                )
            );

            // Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
            $style_row = array(
                'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
                ),
                'borders' => array(
                    'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
                    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
                    'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
                    'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
                )
            );
            
            // Buat header tabel nya pada baris ke 3
            $excel->setActiveSheetIndex(0)->setCellValue('A1', 'NAMA_PENERIMA');
            $excel->setActiveSheetIndex(0)->setCellValue('B1', 'NOMOR_KARTU');
            $excel->setActiveSheetIndex(0)->setCellValue('C1', 'NIK_KTP');
            $excel->setActiveSheetIndex(0)->setCellValue('D1', 'KODE_WILAYAH');
            $excel->setActiveSheetIndex(0)->setCellValue('E1', 'FLAG');
            $excel->setActiveSheetIndex(0)->setCellValue('F1', 'STATUS_REKENING');
            $excel->setActiveSheetIndex(0)->setCellValue('G1', 'STATUS_KARTU');
            $excel->setActiveSheetIndex(0)->setCellValue('H1', 'STATUS_NIK');
            $excel->setActiveSheetIndex(0)->setCellValue('I1', 'NAMA_REKENING_DI_BANK');
            $excel->setActiveSheetIndex(0)->setCellValue('J1', 'NO_KARTU_DI_BANK');
            $excel->setActiveSheetIndex(0)->setCellValue('K1', 'NO_NIK');
            $excel->setActiveSheetIndex(0)->setCellValue('L1', 'WIL');
            $excel->setActiveSheetIndex(0)->setCellValue('M1', 'BANK');
            $excel->setActiveSheetIndex(0)->setCellValue('N1', 'ID_PENGURUS');
            $excel->setActiveSheetIndex(0)->setCellValue('O1', 'NMPROP');
            $excel->setActiveSheetIndex(0)->setCellValue('P1', 'NMKAB');
            $excel->setActiveSheetIndex(0)->setCellValue('Q1', 'NMKEC');
            $excel->setActiveSheetIndex(0)->setCellValue('R1', 'NMKEL');
            $excel->setActiveSheetIndex(0)->setCellValue('S1', 'ALAMAT');
            $excel->setActiveSheetIndex(0)->setCellValue('T1', 'KET');
            $excel->setActiveSheetIndex(0)->setCellValue('U1', 'KETDATA');
            $excel->setActiveSheetIndex(0)->setCellValue('V1', 'NOPESERTAPKH');
            $excel->setActiveSheetIndex(0)->setCellValue('W1', 'KDPROP');
            $excel->setActiveSheetIndex(0)->setCellValue('X1', 'IDBDT');
            $excel->setActiveSheetIndex(0)->setCellValue('Y1', 'IDARTBDT');
            $excel->setActiveSheetIndex(0)->setCellValue('Z1', 'IDPENGURUS');
            $excel->setActiveSheetIndex(0)->setCellValue('AA1', 'NAMA_DTKS');
            $excel->setActiveSheetIndex(0)->setCellValue('AB1', 'NIK_DTKS');
            $excel->setActiveSheetIndex(0)->setCellValue('AC1', 'NOKK_DTKS');
            $excel->setActiveSheetIndex(0)->setCellValue('AD1', 'TGLLAHIR_DTKS');
            $excel->setActiveSheetIndex(0)->setCellValue('AE1', 'NAMAIBU_DTKS');
            $excel->setActiveSheetIndex(0)->setCellValue('AF1', 'FLAGNIK');
            $excel->setActiveSheetIndex(0)->setCellValue('AG1', 'PERCENTILE');
            $excel->setActiveSheetIndex(0)->setCellValue('AH1', 'STADATA');
            $excel->setActiveSheetIndex(0)->setCellValue('AI1', 'STAGANDA');
            $excel->setActiveSheetIndex(0)->setCellValue('AJ1', 'IDKELUARGA');
            $excel->setActiveSheetIndex(0)->setCellValue('AK1', 'KET_TAMBAHAN');
            $excel->setActiveSheetIndex(0)->setCellValue('AL1', 'NOREKENING');
            $excel->setActiveSheetIndex(0)->setCellValue('AM1', 'STATUS_BARU');


            // Apply style header yang telah kita buat tadi ke masing-masing kolom header
            $excel->getActiveSheet()->getStyle('A1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('B1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('C1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('D1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('E1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('F1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('G1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('H1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('I1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('J1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('K1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('L1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('M1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('N1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('O1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('P1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('Q1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('R1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('S1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('T1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('U1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('V1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('W1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('X1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('Y1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('Z1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AA1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AB1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AC1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AD1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AE1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AF1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AG1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AH1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AI1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AJ1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AK1')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('AL1')->applyFromArray($style_col);            
            $excel->getActiveSheet()->getStyle('AM1')->applyFromArray($style_col);            
            
            // get data
            if($type=="B"){
                $myData = $this->Model_transaksi->showDataRevisi($idupload,$prov,$kab)->result();
            } else {
                $myData = $this->Model_transaksi->showData($prov,$kab,$kec,$kel,$ket_tambahan,$nik,$nama)->result();
            }

            $suSC = ['V', 'X'];
            $no = 1; // Untuk penomoran tabel, di awal set dengan 1
            $numrow = 2; // Set baris pertama untuk isi tabel adalah baris ke 4
            foreach($myData as $data){ // Lakukan looping pada variabel siswa
                $excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $data->NAMA_PENERIMA);
                $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $data->NOMOR_KARTU);
                $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $data->NIK_KTP);
                $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $data->KODE_WILAYAH);
                $excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $data->FLAG);
                $excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $data->STATUS_REKENING);
                $excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $data->STATUS_KARTU);
                $excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $data->STATUS_NIK);
                $excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $data->NAMA_REKENING_DI_BANK);
                $excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $data->NO_KARTU_DI_BANK);
                $excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $data->NO_NIK);
                $excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $data->WIL);
                $excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $data->BANK);
                $excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, $data->ID_PENGURUS);
                $excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $data->NMPROP);
                $excel->setActiveSheetIndex(0)->setCellValue('P'.$numrow, $data->NMKAB);
                $excel->setActiveSheetIndex(0)->setCellValue('Q'.$numrow, $data->NMKEC);
                $excel->setActiveSheetIndex(0)->setCellValue('R'.$numrow, $data->NMKEL);
                $excel->setActiveSheetIndex(0)->setCellValue('S'.$numrow, $data->ALAMAT);
                $excel->setActiveSheetIndex(0)->setCellValue('T'.$numrow, $data->KET);
                $excel->setActiveSheetIndex(0)->setCellValue('U'.$numrow, $data->KETDATA);
                $excel->setActiveSheetIndex(0)->setCellValue('V'.$numrow, $data->NOPESERTAPKH);
                $excel->setActiveSheetIndex(0)->setCellValue('W'.$numrow, $data->KDPROP);
                $excel->setActiveSheetIndex(0)->setCellValue('X'.$numrow, $data->IDBDT);
                $excel->setActiveSheetIndex(0)->setCellValue('Y'.$numrow, $data->IDARTBDT);
                $excel->setActiveSheetIndex(0)->setCellValue('Z'.$numrow, $data->IDPENGURUS);
                $excel->setActiveSheetIndex(0)->setCellValue('AA'.$numrow, $data->NAMA_DTKS);
                $excel->setActiveSheetIndex(0)->setCellValue('AB'.$numrow, $data->NIK_DTKS);
                $excel->setActiveSheetIndex(0)->setCellValue('AC'.$numrow, $data->NOKK_DTKS);
                $excel->setActiveSheetIndex(0)->setCellValue('AD'.$numrow, $data->TGLLAHIR_DTKS);
                $excel->setActiveSheetIndex(0)->setCellValue('AE'.$numrow, $data->NAMAIBU_DTKS);
                $excel->setActiveSheetIndex(0)->setCellValue('AF'.$numrow, $data->FLAGNIK);
                $excel->setActiveSheetIndex(0)->setCellValue('AG'.$numrow, $data->PERCENTILE);
                $excel->setActiveSheetIndex(0)->setCellValue('AH'.$numrow, $data->STADATA);
                $excel->setActiveSheetIndex(0)->setCellValue('AI'.$numrow, $data->STAGANDA);
                $excel->setActiveSheetIndex(0)->setCellValue('AJ'.$numrow, $data->IDKELUARGA);
                $excel->setActiveSheetIndex(0)->setCellValue('AK'.$numrow, $data->KET_TAMBAHAN);
                $excel->setActiveSheetIndex(0)->setCellValue('AL'.$numrow, $data->NOREKENING);                                
                
                $excel->getActiveSheet()->setCellValueExplicit('B'.$numrow,$data->NOMOR_KARTU, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('C'.$numrow,$data->NIK_KTP, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('D'.$numrow,$data->KODE_WILAYAH, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('F'.$numrow,$data->STATUS_REKENING, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('G'.$numrow,$data->STATUS_KARTU, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('H'.$numrow,$data->STATUS_NIK, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('K'.$numrow,$data->NO_NIK, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('L'.$numrow,$data->WIL, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('N'.$numrow,$data->ID_PENGURUS, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('V'.$numrow,$data->NOPESERTAPKH, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('W'.$numrow,$data->KDPROP, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('X'.$numrow,$data->IDBDT, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('Y'.$numrow,$data->IDARTBDT, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('Z'.$numrow,$data->IDPENGURUS, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('AB'.$numrow,$data->NIK_DTKS, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('AC'.$numrow,$data->NOKK_DTKS, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('AF'.$numrow,$data->FLAGNIK, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('AG'.$numrow,$data->PERCENTILE, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('AF'.$numrow,$data->STADATA, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('AG'.$numrow,$data->STAGANDA, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('AJ'.$numrow,$data->IDKELUARGA, PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('AL'.$numrow,$data->NOREKENING, PHPExcel_Cell_DataType::TYPE_STRING);
                
                if($type=="B"){
                    $excel->getActiveSheet()->setCellValueExplicit('AM'.$numrow,$data->STATUS_BARU, PHPExcel_Cell_DataType::TYPE_STRING);
                } else {
                    $objValidation = $excel->getActiveSheet()->getCell("AM".$numrow)->getDataValidation();
                    $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                    $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
                    $objValidation->setAllowBlank(false);
                    $objValidation->setShowInputMessage(true);
                    $objValidation->setShowErrorMessage(true);
                    $objValidation->setShowDropDown(false);                
                    $objValidation->setPromptTitle('Status Baru Penerima Bantuan');
                    $objValidation->setPrompt('Silahkan isi dengan V (CLEAN) atau X (NON AKTIF).');
                    $objValidation->setErrorTitle('Input Salah');
                    $objValidation->setError('Nilai tidak sesuai dengan ketentuan, isi dengan huruf V atau X');
                    $objValidation->setFormula1('"'.implode(',', $suSC).'"');
                    unset($objValidation);
                }

                // Apply style row yang telah kita buat tadi ke masing-masing baris (isi tabel)
                $excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('P'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('Q'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('R'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('S'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('T'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('U'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('V'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('W'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('X'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('Y'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('Z'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AA'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AB'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AC'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AD'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AE'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AF'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AG'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AH'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AI'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AJ'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AK'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AL'.$numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('AM'.$numrow)->applyFromArray($style_row);

                $no++; // Tambah 1 setiap kali looping
                $numrow++; // Tambah 1 setiap kali looping
            }

            // Set width kolom
            $excel->getActiveSheet()->getColumnDimension('A')->setWidth(20); // Set width kolom A
            $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Set width kolom B
            $excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
            $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
            $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('J')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('K')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('L')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('M')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('N')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('O')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('P')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('R')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('S')->setWidth(45); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('T')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('U')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('V')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('W')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('X')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('Y')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('Z')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AA')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AB')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AC')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AD')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AE')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AF')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AG')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AH')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AI')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AJ')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AK')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AL')->setWidth(20); // Set width kolom E
            $excel->getActiveSheet()->getColumnDimension('AM')->setWidth(20); // Set width kolom E

            // Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
            $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);

            // Set orientasi kertas jadi LANDSCAPE
            $excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

            // Set judul file excel nya
            $excel->getActiveSheet(0)->setTitle("Data Ganda Penerima Bantuan");
            $excel->setActiveSheetIndex(0);

            // Proses file excel
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Data_Ganda_Penerima_Bantuan_BSP_'.$prov.'_'.$kab.'.xlsx"'); // Set nama file excel nya
            header('Cache-Control: max-age=0');

            $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $write->save('php://output');           
        }                
        
        function upload_data_revisi(){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
            
            $session_data = $this->session->userdata('logged_in');            
            $data['nama_pengguna'] = $session_data['nama_pengguna'];
            $data['username'] = $session_data['username'];
            $data['group_pengguna'] = $session_data['group_pengguna'];
            $data['provinsi_pengguna'] = $session_data['provinsi'];
            
            $data['menu_group_none'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'')->result();
            $data['menu_group_transaksi'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Transaksi')->result();
            $data['menu_group_laporan'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Laporan')->result();
            
            $data['menu'] = $data['menu_group_transaksi'][0]->module_name;
            $data['provinsi'] = $this->Model_transaksi->getProvinsi($session_data['provinsi'])->result();
            $this->load->view('transaksi/upload_data_ganda_revisi', $data);
        }        
        
        public function submit_upload_data_ganda_revisi(){
            $provinsi = $this->input->post('provinsi');
            $kab_kota = $this->input->post('kab_kotax');
            $filename = "data_perbaikan";
            $upload = $this->Model_transaksi->upload_file($filename);            
            
            $file_data = $this->csvimport->get_array($_FILES["data_perbaikan"]["tmp_name"]);
            
            $data = array();
            foreach($file_data as $row){
                $no_kartu = $row["NO KARTU"];
                $nik_ktp = $row["NIK KTP"];
                $idartbdt = $row["IDARTBDT"];
                $id_keluarga = $row["ID KELUARGA"];
                $no_kk = $row["NO KK"];
                $nm_penerima = $row["NAMA PENERIMA"];
                $provinsi = $row["PROPINSI"];
                $kabupaten = $row["KABUPATEN"];
                $kecamatan = $row["KECAMATAN"];
                $kelurahan = $row["KELURAHAN"];
                $status_update = $row["STATUS"];

                array_push($data, array(
                    'IDARTBDT' => $idartbdt,
                    'NIK_KTP' => $nik_ktp,
                    'NOMOR_KARTU' => $no_kartu,
                    'IDKELUARGA' => $id_keluarga,
                    'NOKK_DTKS' => $no_kk,
                    'NAMA_PENERIMA' => $nm_penerima,
                    'NMPROP' => $provinsi,
                    'NMKAB' => $kabupaten,
                    'NMKEC' => $kecamatan,
                    'NMKEL' => $kelurahan,
                    'KET_TAMBAHAN' => $status_update
                ));
            }
            
            //$this->db->update_batch('data_ganda', $data2, 'IDARTBDT');
            $update = $this->Model_transaksi->update_data_multiple($data);
            
            if($update > 0){
                $this->session->set_flashdata('pesan', 'Data Berhasil di Update');
            } else {
                $this->session->set_flashdata('pesan', 'Data Gagal di Update');
            }
            redirect("transaksi/upload_data_revisi");           
        }
        
        public function uploadExcel(){                                                
            // Load plugin PHPExcel nya
            include APPPATH.'third_party/PHPExcel/PHPExcel.php';
            
            $postData = $this->input->post();
            
            $session_data = $this->session->userdata('logged_in');
            
            $userUpload = $session_data['username'];
            $nowDate = date("Y-m-d H:i:s");
            $tglFile = date("dmy")."_".date("His");
            $prov = isset($postData['provinsi'])?$postData['provinsi']:'';
            $kab = isset($postData['kab_kotax'])?$postData['kab_kotax']:'';
            $fileUpload = $_FILES['data_perbaikan']['name'];
            $nmProv = str_replace(" ", "_", $prov);
            $nmKab = str_replace(" ", "_", $kab);
            $noRegister = "REV_DATA_".$nmProv."_".$tglFile;
            
            $insertUpload = array(
                'TGL_UPLOAD' => $nowDate,
                'PROVINSI' => $prov,
                'KABUPATEN' => $kab,
                'FILE_UPLOAD' => $fileUpload,
                'USER_UPLOAD' => $userUpload,
                'NO_REGISTER' => $noRegister
            );
            $prefixTbl = $this->db->dbprefix;
            $insertUploadFile = $this->Model_transaksi->insert_data($prefixTbl.'upload_data_ganda_revisi',$insertUpload);
            $insert_id_upload = $this->db->insert_id();            

            $filename = "data_perbaikan";
            $namaFile = $tglFile."_Data_Perbaikan_".$nmProv."_".$nmKab.".xlsx";
            $upload = $this->Model_transaksi->upload_file($filename,$namaFile);  
                        
            if($prov!="" && $kab!=""){
                $where = "NMPROP='$prov' AND NMKAB='$kab'";
            } elseif($prov!="" && $kab==""){
                $where = "NMPROP='$prov'";
            } else if($prov=="" && $kab!=""){
                $where = "NMKAB='$kab'";
            }
            $totalData = $this->Model_transaksi->check_data($prefixTbl.'data_ganda',$where)->result();
            
            $dataAwal = $totalData[0]->jmldata;
            
            if ($upload['result'] == "success") {

                $data_upload = $this->upload->data();

                $excelreader = new PHPExcel_Reader_Excel2007();
                $loadexcel = $excelreader->load('uploads/'.$data_upload['file_name']); // Load file yang telah diupload ke folder excel
                $sheet = $loadexcel->getActiveSheet()->toArray(null, true, true ,true);

                $data_insert = array();
                $data_update = array();
                $data = array();

                $numrow = 1;
                $jmlInsert = 0;
                $jmlOK = 0;
                $jmlNOTOK = 0;
                
                foreach($sheet as $row){
                    if($numrow > 1){
                        $nama_penerima = $row['A'];
                        $nomor_kartu = $row['B'];
                        $nik_ktp = $row['C'];
                        $kode_wilayah = $row['D'];
                        $flag = $row['E'];
                        $status_rekening = $row['F'];
                        $status_kartu = $row['G'];
                        $status_nik = $row['H'];
                        $nama_rekening_di_bank = $row['I'];
                        $no_kartu_di_bank = $row['J'];
                        $no_nik = $row['K'];
                        $wil = $row['L'];
                        $bank = $row['M'];
                        $id_pengurus = $row['N'];
                        $nmprop = $row['O'];
                        $nmkab = $row['P'];
                        $nmkec = $row['Q'];
                        $nmkel = $row['R'];
                        $alamat = $row['S'];
                        $ket = $row['T'];
                        $ketdata = $row['U'];
                        $nopesertapkh = $row['V'];
                        $kdprop = $row['W'];
                        $idbdt = $row['X'];
                        $idartbdt = $row['Y'];
                        $idpengurus = $row['Z'];
                        $nama_dtks = $row['AA'];
                        $nik_dtks = $row['AB'];
                        $nokk_dtks = $row['AC'];
                        $tgllahir_dtks = $row['AD'];
                        $namaibu_dtks = $row['AE'];
                        $flagnik = $row['AF'];
                        $percentile = $row['AG'];
                        $stadata = $row['AH'];
                        $staganda = $row['AI'];
                        $idkeluarga = $row['AJ'];
                        $ket_tambahan = $row['AK'];
                        $norekening = $row['AL'];
                        $status_new = $row['AM'];
                        if($status_new == "V"){
                            $status = "CLEAN";
                        } elseif($status_new == "X"){
                            $status = "NONAKTIF";
                        } else {
                            $status = "UNCLEAN";
                        }                                                                                                
                        
                        $data_insert = array(
                            'ID_UPLOAD' => $insert_id_upload,
                            'NAMA_PENERIMA' => $nama_penerima,
                            'NOMOR_KARTU' => $nomor_kartu,
                            'NIK_KTP' => $nik_ktp,
                            'KODE_WILAYAH' => $kode_wilayah,
                            'FLAG' => $flag,
                            'STATUS_REKENING' => $status_rekening,
                            'STATUS_KARTU' => $status_kartu,
                            'STATUS_NIK' => $status_nik,
                            'NAMA_REKENING_DI_BANK' => $nama_rekening_di_bank,
                            'NO_KARTU_DI_BANK' => $no_kartu_di_bank,
                            'NO_NIK' => $no_nik,
                            'WIL' => $wil,
                            'BANK' => $bank,
                            'ID_PENGURUS' => $id_pengurus,
                            'NMPROP' => $nmprop,
                            'NMKAB' => $nmkab,
                            'NMKEC' => $nmkec,
                            'NMKEL' => $nmkel,
                            'ALAMAT' => $alamat,
                            'KET' => $ket,
                            'KETDATA' => $ketdata,
                            'NOPESERTAPKH' => $nopesertapkh,
                            'KDPROP' => $kdprop,
                            'IDBDT' => $idbdt,
                            'IDARTBDT' => $idartbdt,
                            'IDPENGURUS' => $idpengurus,
                            'NAMA_DTKS' => $nama_dtks,
                            'NIK_DTKS' => $nik_dtks,
                            'NOKK_DTKS' => $nokk_dtks,
                            'TGLLAHIR_DTKS' => $tgllahir_dtks,
                            'NAMAIBU_DTKS' => $namaibu_dtks,
                            'FLAGNIK' => $flagnik,
                            'PERCENTILE' => $percentile,
                            'STADATA' => $stadata,
                            'STAGANDA' => $staganda,
                            'IDKELUARGA' => $idkeluarga,
                            'KET_TAMBAHAN' => $ket_tambahan,
                            'NOREKENING' => $norekening,
                            'STATUS_BARU' => $status
                        );
                        
                        $insert = $this->Model_transaksi->insert_data($prefixTbl.'data_ganda_revisi',$data_insert);
                        $insert_id = $this->db->insert_id();
                        
                        $jmlInsert += ($insert_id > 0) ? 1 : 0;
                        
                        /*$where = array(
                            'NOMOR_KARTU' => $nomor_kartu
                        );*/
                                                
                        $where = "NOMOR_KARTU='$nomor_kartu'";
                        $cekData = $this->Model_transaksi->check_data($prefixTbl.'data_ganda',$where)->result();                        
                        $jmlCek = count($cekData) > 0 ? $cekData[0]->jmldata : 0;
                        $statusUpload = ($jmlCek > 0) ? "OK" : "NOTOK";
                        
                        $jmlOK += ($statusUpload == "OK") ? 1 : 0;
                        $jmlNOTOK += ($statusUpload == "NOTOK") ? 1 : 0;
                        
                        $data_update = array(
                            'STATUS_DATA' => $statusUpload
                        );

                        $this->Model_transaksi->update_data($where,$data_update,$prefixTbl.'data_ganda_revisi');
                    }
                    $numrow++;
                }                
                
                $where = array('id' => $insert_id_upload);
                $getNoReg = $this->Model_transaksi->showUploadRevisiById($where)->result();                                        
                $noRegUploadRevisi = $getNoReg[0]->NO_REGISTER;

                //upload success
                $this->session->set_flashdata('pesan', '<div class="alert alert-success"><b>PROSES IMPORT BERHASIL!</b><br>No. Registrasi Upload Data: <b>'.$noRegUploadRevisi.'</b><br>Total data berhasil diimport '.$jmlInsert.'.<br>Jumlah Data Sesuai: '.$jmlOK.', Jumlah Data Tidak Sesuai: '.$jmlNOTOK.'</div>');
            } else {
                //upload gagal
                $this->session->set_flashdata('pesan', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL!</b> '.$this->upload->display_errors().'</div>');
            }
            
            redirect('transaksi/upload_data_revisi');
        }
        
        function revisi_surat_permohonan($idpermohonan){
            if(!$this->session->userdata('logged_in'))
            {
                $pemberitahuan = "<div class='alert alert-warning'>Anda harus login dulu </div>";
                $this->session->set_flashdata('pemberitahuan', $pemberitahuan);
                redirect('login');
            }
            
            $session_data = $this->session->userdata('logged_in');            
            $data['nama_pengguna'] = $session_data['nama_pengguna'];
            $data['username'] = $session_data['username'];
            $data['group_pengguna'] = $session_data['group_pengguna'];
            $data['provinsi_pengguna'] = $session_data['provinsi'];
            $data['kabupaten_pengguna'] = $session_data['kabupaten'];
            
            $data['menu_group_none'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'')->result();
            $data['menu_group_transaksi'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Transaksi')->result();
            $data['menu_group_laporan'] = $this->Model_group_access->showParentMenuGroup($session_data['group_pengguna'],'Laporan')->result();            
            
            $data['menu'] = "surat_permohonan";
            $prefixTbl = $this->db->dbprefix;
            $where = array('id' => $idpermohonan);
            $data['data_surat'] = $this->Model_transaksi->showPermohonanById($where,$prefixTbl."surat_permohonan_data_ganda")->result();
            $data['provinsi_surat'] = $data['data_surat'][0]->nm_propinsi;
            $data['kabupaten_surat'] = $data['data_surat'][0]->nm_kabupaten;
            $data['noregister_surat'] = $data['data_surat'][0]->no_registrasi_revisi;
            $data['surat_permohonan'] = $data['data_surat'][0]->nm_surat_permohonan;
            $data['lampiran_dokumen'] = $data['data_surat'][0]->nm_lampiran_dokumen;
            $data['alasan_tolak'] = $data['data_surat'][0]->alasan_tolak;
            $data['provinsi'] = $this->Model_transaksi->getProvinsi($session_data['provinsi'])->result();
            $data['kab_kota'] = $this->Model_transaksi->getKabKota($data['provinsi_surat'],$session_data['kabupaten'])->result();
            $data['noregister'] = $this->Model_transaksi->getNoRegDataRevisi($session_data['provinsi'],$session_data['kabupaten'])->result();
            $data['id_permohonan'] = $idpermohonan;
            $this->load->view('transaksi/surat_permohonan_revisi', $data);
        }
        
        function submit_upload_surat_revisi(){                        
            $session_data = $this->session->userdata('logged_in');            
            $nama_pengguna = $session_data['nama_pengguna'];
            $idSurat = $this->input->post('idsuratpermohonan');
            $tgl_permohonan = date("Y-m-d");
            $provinsi = $this->input->post('provinsi');
            $kab_kota = $this->input->post('kab_kota');
            $no_registrasi_revisi = $this->input->post('noregister');
            $nm_surat_permohonan = $_FILES['surat_permohonan']['name'];
            $nm_surat_permohonan_old = $this->input->post('surat_permohonan_old');
            $nm_lampiran_dokumen = $_FILES['lampiran_dokumen']['name'];
            $nm_lampiran_dokumen_old = $this->input->post('lampiran_dokumen_old');
            $dateUpload = date("dmy");
            
            $this->load->library('upload');
            
            if($nm_surat_permohonan!=""){
                $fileSurat = $dateUpload."_Surat_Permohonan_".$provinsi."_".$kab_kota.".pdf";
                $config['upload_path'] = './uploads/';
                $config['allowed_types'] = 'pdf';
                $config['file_name'] = $fileSurat;

                $this->upload->initialize($config);
                $this->upload->do_upload('surat_permohonan');
                $result1 = $this->upload->data();
            } else {
                $result1 = "";
            }
            
            if($nm_lampiran_dokumen!=""){
                $fileLampiran = $dateUpload."_Lampiran_Data_".$provinsi."_".$kab_kota.".pdf";
                $config2['upload_path'] = './uploads/';
                $config2['allowed_types'] = 'pdf';
                $config2['file_name'] = $fileLampiran;

                $this->upload->initialize($config2);        
                $this->upload->do_upload('lampiran_dokumen');
                $result2 = $this->upload->data();
            } else {
                $result2 = "";
            }
            
            $result = array('surat_permohonan'=>$result1,'lampiran_dokumen'=>$result2);
            $namaSurat = ($result1!="") ? $result['surat_permohonan']['file_name'] : $nm_surat_permohonan_old;
            $namaLampiran = ($result2!="") ? $result['lampiran_dokumen']['file_name'] : $nm_lampiran_dokumen_old;
            
            $data = array(
                'tgl_permohonan' => $tgl_permohonan,
                'nm_pemohon' => $nama_pengguna,
                'nm_propinsi' => $provinsi,
                'nm_kabupaten' => $kab_kota,
                'nm_surat_permohonan' => $namaSurat,
                'nm_lampiran_dokumen' => $namaLampiran,
                'status_permohonan' => 'Open',
                'nm_pengecek' => '',
                'alasan_tolak' => '',
                'tgl_acc_dinsos' => '',
                'acc_dinsos_by' => '',
                'tgl_acc_provinsi' => '',
                'acc_provinsi_by' => '',
                'tgl_acc_pfm' => '',
                'acc_pfm_by' => '',
                'tgl_update_revisi' => $tgl_permohonan,
                'update_revisi_by' => $nama_pengguna,
                'no_registrasi_revisi' => $no_registrasi_revisi
            );
        
            $where = array(
                'id' => $idSurat
            );

            $prefixTbl = $this->db->dbprefix;
            $this->Model_transaksi->update_data($where,$data,$prefixTbl.'surat_permohonan_data_ganda');
            
            if($insert > 0){
                $this->session->set_flashdata('pesan', 'Dokumen Berhasil Terkirim');
            } else {
                $this->session->set_flashdata('pesan', 'Dokumen Tidak Berhasil Terkirim');
            }
            redirect('transaksi/upload_surat');
        }
    }
    
?>