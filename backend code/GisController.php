<?php

class GisController extends Controller {
		
		public function actionIndex() {
			$this->renderPartial("index");
			// echo " Index of StudentController";
		}

		public function actiontestPusher(){
			
		}

		public function actiontukarPoinRupiah($sender, $poin) {
			$biaya = 5000;
			$poin += $biaya;
				// $model = UserPoin::model()->find("username = '$sender' ");
			$sql = "select sum(poin) as poin from user_poin where username = '$sender'";
			$model = Yii::app()->db->createCommand($sql)->queryRow();
			if ($poin-$biaya < 50000 ){
				$array = array("success"=>false,"message"=>"Poin tidak mencukupi, Saat ini poin anda adalah :  $model[poin] ","err"=>array("Transaksi tidak dapat diproses ")) ;
				echo json_encode($array);	
				return false;
			}

			if ($model['poin']>=$poin){ //jika memiliki poin yang cukup

				$ukm = Ukm::model()->findByPk($receiver);
				
				$poin_s = new UserPoin;
				$poin_s->created_date = date("Y-m-d H-i:s");
				$poin_s->ukm_id = 0;
				$poin_s->poin = $poin*-1;
				$poin_s->username = $sender;
				if ($poin_s->save()){
					$array = array("success"=>true,"message"=>"Transaksi Berhasil ! ");
					echo json_encode($array);
				}else{
					echo json_encode(array("success"=>false,"err"=>$poin_s->getErrors()));
				}
			}else{
				$array = array("success"=>false,"message"=>"Poin tidak mencukupi, Saat ini poin anda adalah :  $model[poin] ","err"=>array("Transaksi tidak dapat diproses ")) ;
				echo json_encode($array);	
			}

		}
		public function actionTransfer($sender, $receiver,$poin) {
			// $model = UserPoin::model()->find("username = '$sender' ");
			$sql = "select sum(poin) as poin from user_poin where username = '$sender'";
			$model = Yii::app()->db->createCommand($sql)->queryRow();
			if ($model['poin']>=$poin){ //jika memiliki poin yang cukup

				$ukm = Ukm::model()->findByPk($receiver);
				
				$poin_s = new UserPoin;
				$poin_s->created_date = date("Y-m-d H-i:s");
				$poin_s->ukm_id = 0;
				$poin_s->poin = $poin*-1;
				$poin_s->username = $sender;
				if ($poin_s->save()){
					$poin_r = new UserPoin;
					$poin_r->created_date = date("Y-m-d H-i:s");
					$poin_r->poin = $poin;
					$poin_r->ukm_id = 0;
					$poin_r->username = $ukm->username;
					if ($poin_r->save()){
						$array = array("success"=>true,"message"=>"Transaksi Berhasil ! ");
						echo json_encode($array);
					}else{
						echo json_encode(array("success"=>false,"err"=>$poin_r->getErrors()));
					}
				}else{
					echo json_encode(array("success"=>false,"err"=>$poin_s->getErrors()));
				}
			}else{
				$array = array("success"=>false,"message"=>"Poin tidak mencukupi, Saat ini poin anda adalah :  $model[poin] ","err"=>array("Transaksi tidak dapat diproses ")) ;
				echo json_encode($array);	
			}

		}
		public function jumlahFavoritku($username){
			$sql = "select count(*) as jml from user_jenis_favorite where username = '$username' ";
			$model = Yii::app()->db->createCommand($sql)->queryRow();
			if ($model>0){
				return $model['jml'];
			}else{
				return 0;
			}
		}
		public function actionGetSlider($username) {
			

			if ($this->jumlahFavoritku($username)>0){
				if ($username!="null"){
					$where = " and  k.nama  in (select jenis from user_jenis_favorite where username = '$username' )";
				}
			}

			$now = date("Y-m-d");	
			// $this->renderPartial("index ");
			$sql = " 
			select 
			u.id ukm_id,
			u.nama,

			if (tipe=3,'Tukang Dagang Tidak Tetap',u.alamat) as alamat,
			u.lon,
			u.lat,
			u.keterangan,
			u.isaktif,
			u.jam,
			u.rt,
			u.rw,
			u.subkategori,
			u.tipe,
			u.tanggal_input,
			u.tanggal_akhir,
			u.kelurahan,
			u.pemilik,
			u.telepon,
			u.rekomender,
			u.username,
			u.kategori,
			u.gambar,
			u.kendaraan,
			 k.nama nama_kategori, u.nama nama_umkm
			 from iklan i 
			inner join ukm u on u.id = i.ukm_id 
			inner join kategori k  on k.id = u.kategori
			 where 
			date('$now')
			BETWEEN date(i.tanggal_mulai) and date(i.tanggal_akhir) 
			$where
			group by u.id
			order by i.tanggal_mulai asc 
			";
			$model = Yii::app()->db->createCommand($sql)->queryAll();
			echo json_encode($model);

			// echo " Index of StudentController";
		}

		public function actionKonfirmasiIklan($ukm_id) {
			// echo "123";
		$transaction = Yii::app()->db->beginTransaction();
			try {
				$count = Ukm::model()->findByPk($ukm_id);
				if (count($count)>0){
					$username = Ukm::model()->findByPk($ukm_id)->telepon;
					// echo $this->cekPoin($username)."123";
					if ($this->cekPoin($username)>=1000){
						if ($this->cekExist($ukm_id)<1){

							$model = new Iklan;
							$model->ukm_id  = $ukm_id;
							$model->tanggal_mulai = date("Y-m-d ");
							$tgl_akhir = date('Y-m-d',strtotime(date("Y-m-d ") . " + 7 day"));
							$model->tanggal_akhir = $tgl_akhir;
							if ($model->save()){
								$poin = new UserPoin;
								$poin->username = $username;
								$poin->ukm_id = $ukm_id;
								$poin->created_date = date("Y-m-d");
								$poin->poin = -5000;
								if ($poin->save()){
									$transaction->commit();

									$content = "Selamat!! Iklan anda akan aktif selama waktu 7 hari, sampai dengan $tgl_akhir
										\n\nTim Tukang Dagang";
										$subject = "Terimakasih telah memasang iklan ";
										if (TelkomController::kirimSMS($username,$content)){
											echo json_encode(array("success"=>true));
										}else{
											echo json_encode(array("success"=>true));
										}

								}else{
									echo json_encode(array("success"=>false,"err"=>$poin->getErrors()));
								}
							}
						}//end exist
						else{
							echo json_encode(array("success"=>false,"err"=>array("12"=>"Tidak bisa menampilkan iklan, Anda memiliki Iklan Aktif") ));					
						}


					}else{ // end cek point
						echo json_encode(array("success"=>false,"err"=>array("er2"=>"Anda tidak memiliki cukup poin") ));
					}
				}//end count
				else{
					echo json_encode(array("success"=>false,"err"=>array("er2"=>"Anda tidak memiliki UMKM") ));					
				}
			}catch (Exception $e) { // end try catch
				echo json_encode(array("success"=>false,"err"=>$e));
				$transaction->rollback();
			}
				// echo " Index of StudentController";
		}

		public function actionCancelVerification($username) {
			$model = User::model()->findByPk($username);
			if (count($model)>=1){
				if ($model->delete())
					echo json_encode(array("success"=>true));
				else
					echo json_encode(array("success"=>false,"err"=>$model->getErrors()));
			}else{
					echo json_encode(array("success"=>true));
			}


		}
		public function actionDeleteFavorite($username,$ukm_id) {
			$model = UkmFavorite::model()->find("username = '$username' and ukm_id='$ukm_id' ");
			if ($model->delete()){
				echo json_encode(array("success"=>true));
			}else{
				echo json_encode(array("success"=>false));
			}
		}
		public function actionSetFavorite($ukm_id,$username) {
			$cek = UkmFavorite::model()->find("username = '$username' and ukm_id = $ukm_id ");
			if (count($cek)==0){
				$poin = new UkmFavorite;
				$poin->ukm_id = $ukm_id;
				$poin->username = $username;
				$poin->tanggal_input= date("Y-m-d H:i:");
				if ($poin->save())
					echo json_encode(array("success"=>true));
				else
					echo json_encode(array("success"=>false,"err"=>$poin->getErrors()));
			}else{
				echo json_encode(array("success"=>false,"err"=>array("Error"=>"Anda telah menambahkan Tukang Dagang ini ke Daftar Favorite") ));
			}
		}

		public function actionSetverifikasi($ukm_id,$username) {
			$model = Ukm::model()->findByPk($ukm_id);
			// $model->tanggal_akhir= date("Y-m-d");
			$model->tanggal_akhir = date('Y-m-d',strtotime(date("Y-m-d ") . " + 3 month"));
			if ($model->update())
				echo json_encode(array("success"=>true));
			else
				echo json_encode(array("success"=>false,"err"=>$model->getErrors()));
			// }else{
			// 	echo json_encode(array("success"=>false,"err"=>array("Error"=>"Anda telah menambahkan UMKM ini ke Daftar Favorite") ));
			
		}
		public function actionSetTolak($ukm_id) {
			$model = Ukm::model()->findByPk($ukm_id);
			$model->isaktif = 2;
			if ($model->update()){
				echo json_encode(array("success"=>true));
			}


		}
		public function actionKonfirmasi($ukm_id,$status,$telepon) {
			Yii::import('application.controllers.*'); 
			$transaction = Yii::app()->db->beginTransaction();
			try {


			
			$ukm = Ukm::model()->find("username = $telepon");

			if (count($ukm)==0){
			

			$model = Ukm::model()->findByPk($ukm_id);
			$model->isaktif = $status;
			$model->telepon = $telepon;
			$model->username = $telepon;
			if ($model->update()){
				if ($status==1){
					
					if ($model->rekomender) 

					$poin = new UserPoin;
					$poin->username = $model->rekomender;
					$poin->ukm_id = $model->id;
					$poin->created_date = date("Y-m-d");
					$poin->poin = 5000;
					if ($poin->save()){

						if ($model->rekomender!=$telepon){ // yang d ajukan dapat poin
							$poin_b = new UserPoin;
							$poin_b->username = $model->telepon;
							$poin_b->ukm_id = "00";
							$poin_b->created_date = date("Y-m-d");
							$poin_b->poin = 5000;
							$poin_b->save();
						}


						//simpan jam..
						$status_op = array();
						for ($x=1;$x<=7;$x++):
							$ukmop = new UkmOp;
							$ukmop->hari = $x;
							
							if ($x==7){
								$ukmop->jam_buka = "-";
								$ukmop->jam_tutup = "-";		
							}else{
								$ukmop->jam_buka = "07:00";
								$ukmop->jam_tutup = "22:00";
							}
							$ukmop->jam_buka = "07:00";
							$ukmop->is_tutup = 1;
							$ukmop->jam_tutup = "22:00";
							$ukmop->ukm_id = $model->id;
							if ($ukmop->save())
								array_push($status_op, true);
							else{
								array_push($status_op, false);
								$msg = $ukmop->getErrors();
							}

							//if (!$ukmop->save())
							//	echo json_encode(array("success"=>false,"err"=>$ukmop->getErrors())); 
						endfor;

						if (in_array(false, $status_op)){
							echo json_encode(array("success"=>false,"err"=>"Tidak bisa "));
						}else{
							// if (!UserController::isHasUMKM($model->rekomender)){ // jika  username belum  memiliki ukm ?
							if (User::model()->count("username = '$telepon' ") < 1 ){
								$user = new User;
								$user->username = $telepon;
								$user->password  = $telepon;
								$user->level = 2;
								$user->isVerified = 1;
								$user->isSigning = 0;
								if ($user->save()){
									$transaction->commit();
									echo json_encode(array("success"=>true));
								}else{
									echo json_encode(array("success"=>false,"err"=>$user->getErrors()));
								}
							}else{ // ubah status ukm menjadi aktif
								$ukm = Ukm::model()->find(" id = '$ukm_id' ");
								$ukm->isaktif = 1;
								if ($ukm->update()){
									$transaction->commit();
									echo json_encode(array("success"=>true));
								}
							}
						}	


					}
					else
						echo json_encode(array("success"=>false,"err"=>$poin->getErrors() ) );


				}
			}
			else
				echo json_encode(array("success"=>false,"err"=>array("error when call model->update ")));



			}else{

				echo json_encode(array("success"=>false,"err"=>array("error2"=>"No telah dimiliki oleh UMKM lain") ));
			}

			}catch (Exception $e) { // end try catch
				echo json_encode(array("success"=>false,"err"=>array("error"=>"catch ".$e) ) );
				$transaction->rollback();
			}

		}
		public function cekPoin($username){
			$sql_poin = "select sum(poin) poin from user_poin WHERE username = '$username' group by username ";
			$poin = Yii::app()->db->createCommand($sql_poin)->queryRow();
			return $poin['poin'];

		}
		public function cekExist($ukm_id){
			$date = date("Y-m-d");
			$sql_poin = "select * from iklan where date(tanggal_mulai)<=date('$date') and date(tanggal_akhir)>=date('$date') and  ukm_id = '$ukm_id'  ";
			$poin = Yii::app()->db->createCommand($sql_poin)->queryAll();
			return count($poin);

		}
		public function actionupdateLokasi($username,$lat,$lng) {
			$model = User::model()->find("username = '$username' ");
			$model->last_lng = $lng;
			$model->last_lat = $lat;
			$model->isSigning = 1;
			if ($model->update()){
				echo json_encode(array("success"=>true,"err"=>"Zero Result"));
			}

		}
		public function actionUpdateTrackingStatus($username,$ukm_dest) {
			$model = UkmTracking::model()->find("username = '$username' and ukm_id = '$ukm_dest'  ");
			if (count($model)<1){
				$model = new UkmTracking;
			}


			$model->ukm_id = $ukm_dest;
			$model->username = $username;
			$model->waktu = date("Y-m-d H:i:s");
			if ($model->save()){
				echo json_encode(array("success"=>true,"err"=>"NULL"));
			}else{
				echo json_encode(array("success"=>false,"err"=>$model->getErrors()));
			}

		}
		public function actionRefreshUserData() {
			// if (isset(var))
			if (isset($_REQUEST['username'])){
					$username = $_REQUEST['username'];
					if (isset($username)){
						$model = User::model()->find("username = '$username'  ");
						 $sql = "select 
						 *,DATE_FORMAT(tanggal_akhir,'%d %b %y') tanggal_akhir,
						  DATEDIFF(tanggal_akhir,NOW()) sisa
						  from ukm where telepon = '$username' ";
						 $ukm = Yii::app()->db->createCommand($sql)->queryRow();

						 $sql_poin = "select sum(poin) poin from user_poin WHERE username = '$username' group by username ";
						 $poin = Yii::app()->db->createCommand($sql_poin)->queryRow();
						 if ($poin==false){
						 	$poin = array("poin"=>0);
						 }
 
						// $ukm = Ukm::model()->find("telepon = '$username' ");
						if ($model){
							$data = array(
								"status"=>true,
								"level"=>$model->level,
								"username"=>$username,
								"ukm"=>$ukm,
								"poin"=>$poin
							);
							echo json_encode($data);
						}else{
							echo json_encode(array("status"=>false,"err"=>"Zero Result"));
						}
					}
			}else{
				echo json_encode(array("status"=>false,"err"=>"Zero Result"));
				
			}
			// echo "masuk loguin";

			// $this->renderPartial("index");
			// echo " Index of StudentController";
		}
		public function rpHash($value) {
			$hash = 5381; 
		    $value = strtoupper($value); 
		    for($i = 0; $i < strlen($value); $i++) { 
		        $hash = ($this->leftShift32($hash, 5) + $hash) + ord(substr($value, $i)); 
		    } 
		    return $hash;
		}
		public function  leftShift32($number, $steps) { 
		    // convert to binary (string) 
		    $binary = decbin($number); 
		    // left-pad with 0's if necessary 
		    $binary = str_pad($binary, 32, "0", STR_PAD_LEFT); 
		    // left shift manually 
		    $binary = $binary.str_repeat("0", $steps); 
		    // get the last 32 bits 
		    $binary = substr($binary, strlen($binary) - 32); 
		    // if it's a positive number return it 
		    // otherwise return the 2's complement 
		    return ($binary{0} == "0" ? bindec($binary) : 
		        -(pow(2, 31) - bindec(substr($binary, 1)))); 
		} 
		public function actionLogout($username) {
			try {

				$model = User::model()->find(" username = '$username' ");
				if (count($model)>0){
					$model->isSigning = 0;
					$model->last_lng = " ";
					$model->last_lat = " ";
					if ($model->update()){
						echo json_encode(array("success"=>true));
					}else{
						echo json_encode(array("success"=>false));
					}
				}else{
					echo json_encode(array("success"=>true));	
				}
			}catch(Exception $err){
				echo json_encode(array("success"=>true));
			}

		}
		public function actionLogin() {
			if ($this->rpHash($_REQUEST['defaultReal']) == $_REQUEST['defaultRealHash']) { // jika captcha benar
					if (isset($_REQUEST['username']) && isset($_REQUEST['password'])){
							$username = $_REQUEST['username'];
							$password = $_REQUEST['password'];
							if (isset($username) && isset($password)){
								$model = User::model()->find("username = '$username' and password='$password' and isVerified=1 ");
								 $sql = "select 
								 *,DATE_FORMAT(tanggal_akhir,'%d %b %y') tanggal_akhir,
								  DATEDIFF(tanggal_akhir,NOW()) sisa
								  from ukm where telepon = '$username' ";
								 $ukm = Yii::app()->db->createCommand($sql)->queryRow();

								 $sql_poin = "select sum(poin) poin from user_poin WHERE username = '$username' group by username ";
								 $poin = Yii::app()->db->createCommand($sql_poin)->queryRow();

								 $query_favorite = "select jenis from user_jenis_favorite where username = '$username' ";
								 $model_fav = Yii::app()->db->createCommand($query_favorite)->queryAll();


								 $query_td_favorite = "select ukm_id from ukm_favorite where username = '$username' ";
								 $model_td_fav = Yii::app()->db->createCommand($query_td_favorite)->queryAll();




		 
								// $ukm = Ukm::model()->find("telepon = '$username' ");
								if ( count($model)!=0 ){
									$data = array(
										"status"=>true,
										"level"=>$model->level,
										"isfirstlogin"=>$model->isFirstLogin,
										"username"=>$username,
										"kategori_favorite"=>$model_fav,
										"td_favorite"=>$model_td_fav,
										"ukm"=>$ukm,
										"poin"=>$poin
									);
									echo json_encode($data);
									$model->isSigning = 1;
									$model->isFirstLogin = 0;
									$model->last_lat = $_REQUEST['last_lat'];
									$model->last_lng = $_REQUEST['last_lng'];
									$model->update();
								}else{
									echo json_encode(array("status"=>false,"login"=>"Username atau password salah"));
								}
							}
					}else{
						echo json_encode(array("status"=>false,"login"=>"Tidak boleh kosong"));
						
					}
			}else{
				echo json_encode(array("status"=>false,
					"login"=>"Captcha Salah" )
				);
			}
			// echo "masuk loguin";

			// $this->renderPartial("index");
			// echo " Index of StudentController";
		}
		public function actionSetVerified($username) {
			$user = User::model()->findByPk($username);
			$user->isVerified = 1;
			if ($user->update())
				echo json_encode(array("success"=>true));
			else
				echo json_encode(array("success"=>false));
		}
		public function actionVerifikasi() {
			ini_set('display_errors', 1);

			ini_set('display_startup_errors', 1);

			error_reporting(E_ALL);

			$phone = $_REQUEST["phone_verifikasi"];
			$kode = $_REQUEST["kode_verifikasi"];
			$model = User::model()->findByPk($phone);

			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "http://api.mainapi.net/smsotp/1.0.1/otp/$phone/verifications",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "otpstr=$kode&digit=4",
			  CURLOPT_HTTPHEADER => array(
			    "Authorization: Bearer bfdd35ca8e67275aa7d463f82454c076",
			    "Cache-Control: no-cache",
			    "Content-Type: application/x-www-form-urlencoded",
			    "Postman-Token: 28c86ef7-66a9-6a52-ec2a-95de15e7541d",
			    "accept: application/json"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);	

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
				// print_r($response);
				$data = json_decode($response, TRUE);
				if ($data['status']==true){
					$model->isVerified = 1;
					if ($model->update())
						// if ($this->kirimSMS($phone)){
						$content = "Selamat Datang di Aplikasi Tukang Dagang , Aplikasi yang dapat memberikan informasi, memanggil & memesan Tukang Dagang sekitarmu

						<br><br>
						Best Regards
						Tim Tukang Dagang";
						$subject = "Terimakasih telah bergabung dengan Tukang Dagang";
						if (TelkomController::HelioKirimEmail($model->email,$subject,$content)){
							echo json_encode(array("success"=>true));
						}else{
							echo json_encode(array("success"=>false,"messages"=>"Gagal Kirim Email"));
						}

				}else{
					echo $response;
					// echo "error";
				}
				// print_r($data);
				// echo json_decode($response);

				
			  // echo $response;
			}

			// if ($kode==$model->verifyCode){
			// 	$model->isVerified = 1;
			// 	if ($model->update())
			// 		echo json_encode(array("success"=>true));
			// 	else
			// 		echo json_encode(array("success"=>false,"err"=>$model->getErrors(0)));
			// }else{
			// 	echo json_encode(array("success"=>false));
			// }
			
		}
		public  function verifikasiHelio()
		{	
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://api.mainapi.net/helio/1.0.1/login",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "{\r\n  \"email\": \"admin@35utech.com\",\r\n  \"password\": \"Try08986044235\"\r\n}",
			  CURLOPT_HTTPHEADER => array(
			    "Accept: application/json",
			    "Authorization: Bearer bfdd35ca8e67275aa7d463f82454c076",
			    "Cache-Control: no-cache",
			    "Content-Type: application/json",
			    "Postman-Token: 74952ade-e7db-0ec7-06e9-5d47e651f72b"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
				$data = json_decode($response,TRUE);
				if ($data['message']=='ok'){
					return $data['result']['user']['token'];
					// echo 
				}else{
					echo "<pre>";
					print_r($data);
					echo "</pre>";
					echo "error";
				}
			  // echo $response;
			}
		}


		// public function HelioKirimEmail($email,$subject,$content){
		// 	// exit;
		// 	$token = TelkomController::verifikasiHelio();

		// 	$curl = curl_init();

		// 	curl_setopt_array($curl, array(
		// 	  CURLOPT_URL => "https://api.mainapi.net/helio/1.0.1/sendmail",
		// 	  CURLOPT_RETURNTRANSFER => true,
		// 	  CURLOPT_ENCODING => "",
		// 	  CURLOPT_MAXREDIRS => 10,
		// 	  CURLOPT_TIMEOUT => 30,
		// 	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 	  CURLOPT_CUSTOMREQUEST => "POST",
		// 	  CURLOPT_POSTFIELDS => "{\r\n  \"token\": \"$token\",\r\n  \"subject\": \"Hai\",\r\n  \"to\": \"$email\",\r\n  \"body\": \"$content\"\r\n}",
		// 	  CURLOPT_HTTPHEADER => array(
		// 	    "Authorization: Bearer bfdd35ca8e67275aa7d463f82454c076",
		// 	    "Cache-Control: no-cache",
		// 	    "Postman-Token: f4f57150-95e1-9870-a65a-861db654a2af"
		// 	  ),
		// 	));

		// 	$response = curl_exec($curl);
		// 	$err = curl_error($curl);

		// 	curl_close($curl);


		// 	if ($err) {
		// 	  echo "cURL Error #:" . $err;
		// 	} else {
		// 	  // echo $response;
		// 		$data = json_decode($response,TRUE);
		// 		if ($data['status']==200){
		// 			return true;
		// 		}else{
		// 			return false;
		// 		}
					
		// 			// return $data['result']['user']['token'];
		// 	}
					


		// }

		// public function kirimSMS($username){
		

		// 	$curl = curl_init();

		// 	curl_setopt_array($curl, array(
		// 	  CURLOPT_URL => "http://api.mainapi.net/smsnotification/1.0.0/messages",
		// 	  CURLOPT_RETURNTRANSFER => true,
		// 	  CURLOPT_ENCODING => "",
		// 	  CURLOPT_MAXREDIRS => 10,
		// 	  CURLOPT_TIMEOUT => 30,
		// 	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 	  CURLOPT_CUSTOMREQUEST => "POST",
		// 	  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"msisdn\"\r\n\r\n$username\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"content\"\r\n\r\n  Selamat Anda terdaftar pada  aplikasi Tukang Dagang :) \r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
		// 	  CURLOPT_HTTPHEADER => array(
		// 	    "Authorization: Bearer bfdd35ca8e67275aa7d463f82454c076",
		// 	    "Cache-Control: no-cache",
		// 	    "Postman-Token: c0f32c0a-6f5a-9047-efe1-0ab50ad56eaf",
		// 	    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
		// 	  ),
		// 	));

		// 	$response = curl_exec($curl);
		// 	$err = curl_error($curl);

		// 	curl_close($curl);

		// 	if ($err) {
		// 	  echo "cURL Error #:" . $err;
		// 	} else {
		// 		$data = json_decode($response, TRUE);
		// 		if ($data['status']=="SUCCESS"){
		// 			return true;
		// 		}else{
		// 			return false;
		// 		}

		// 	  // echo $response;
		// 	}
		// }
		public function actionRegister() {
			// if (isset(var))
			$transaction = Yii::app()->db->beginTransaction();
			try {
			Yii::import('ext.sms.smsGateway', true);

			if (isset($_REQUEST['username']) && isset($_REQUEST['password'])){
					$username = $_REQUEST['username'];
					$password = $_REQUEST['password'];
					$email = $_REQUEST['email'];
					if (isset($username) && isset($password)){
						$model = new User;
						$model->username = $username;
						$model->password  = $password;
						$model->email  = $email;
						$model->level = 2;
						$model->isVerified = 0;
						$rand = rand(100,999);
						$model->verifyCode = $rand;





						if ($model->save()){


							// tes

							// $curl = curl_init();
							ini_set('display_errors', 1);

							ini_set('display_startup_errors', 1);

							error_reporting(E_ALL);

							$data = TelkomController::kirimSMSOtp($username);
							if ($data['status']==true){
								// return true;
								// return $data['status'];
								
								$data = array(
									"response"=>array("success"=>true),
									"status"=>true,
									// "number"=>$rand,	
									"phone"=>$username

								);
								$transaction->commit();
								echo json_encode($data);
							}else{
								echo $response;
								// echo "error";
							}
						


							// curl_setopt_array($curl, array(
							//   CURLOPT_URL => "http://api.mainapi.net/smsotp/1.0.1/otp/$username",
							//   CURLOPT_RETURNTRANSFER => true,
							//   CURLOPT_ENCODING => "",
							//   CURLOPT_MAXREDIRS => 10,
							//   CURLOPT_TIMEOUT => 30,
							//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							//   CURLOPT_CUSTOMREQUEST => "PUT",
							//   CURLOPT_POSTFIELDS => "phoneNum=$username&digit=4",
							//   CURLOPT_HTTPHEADER => array(
							//     "Authorization: Bearer bfdd35ca8e67275aa7d463f82454c076",
							//     "Cache-Control: no-cache",
							//     "Content-Type: application/x-www-form-urlencoded",
							//     "Postman-Token: a24d0b85-5d02-8d26-841a-b6a6ffa73507",
							//     "accept: application/json"
							//   ),
							// ));

							// $response = curl_exec($curl);
							// $err = curl_error($curl);

							// curl_close($curl);

							// if ($err) {
							//   echo "cURL Error #:" . $err;
							// } else {
							// 	 $data = json_decode($response, TRUE);
							// 	if ($data['status']==true){
							// 		$data = array(
							// 			"response"=>array("success"=>true),
							// 			"status"=>true,
							// 			// "number"=>$rand,	
							// 			"phone"=>$username

							// 		);
							// 		$transaction->commit();
							// 		echo json_encode($data);
							// 	}else{
							// 		echo $response;
							// 		// echo "error";
							// 	}
							// }
							exit;
							// tes

							
							
						}else{
							echo json_encode(array("response"=>array("success"=>false,"err"=>$model->getErrors()) ));
							// echo json_encode(array("success"=>false,"err"=>$model->getErrors()));
						}
					}
			}else{
				echo json_encode(
					array("response"=>array("success"=>false,"err"=>"Tidak boleh kosong")));
				
			}

			}catch(Exception $err){
				echo json_encode(array("err"=>"123 :".$err));
				$transaction->rollback();
			}
			// echo "masuk loguin";

			// $this->renderPartial("index");
			// echo " Index of StudentController";
		}
		
		public function actionGetPoin($username) {
		$sql = "select sum(poin) as poin from user_poin where username = '$username'";
		$model = Yii::app()->db->createCommand($sql)->queryRow();
		if ($model['poin']!=""){
	        echo json_encode($model);
		}else{
			echo json_encode(array("poin"=>0) );
		}


		}
		public function actionGetPending() {
			$sql = "
	      select DATE_FORMAT(tanggal_input,'%d %b %y %h:%i') tinput ,
	      u.*, icon, k.nama nama_kategori, kel.nama nama_kelurahan, kec.nama nama_kecamatan
	      from ukm u  
	      inner join kategori k  on k.id = u.kategori
	      left join kelurahan kel on kel.id = u.kelurahan
	      left join kecamatan kec on kec.id = kel.kecamatan_id

	       where  u.isaktif = 0
	       order by tanggal_input desc

	      ";
	      // echo $sql;
	      $model = Yii::app()->db->createCommand($sql)->queryAll();
	      echo json_encode($model);

		}

		public function actionGetFavorite($username) {
			$sql = "
	      select DATE_FORMAT(uf.tanggal_input,'%d %b %y %h:%i') tinput ,
	      u.*, icon, k.nama nama_kategori, kel.nama nama_kelurahan, kec.nama nama_kecamatan
	      from ukm u  
	      inner join kategori k  on k.id = u.kategori
	      left join kelurahan kel on kel.id = u.kelurahan
	      left join kecamatan kec on kec.id = kel.kecamatan_id
	      inner join ukm_favorite uf on uf.ukm_id = u.id

	       where  uf.username = '$username'
	       order by uf.tanggal_input desc
	       

	      ";
	      // echo $sql;
	      $model = Yii::app()->db->createCommand($sql)->queryAll();
	      echo json_encode($model);

		}
		public function actiongetListPanggilan($ukm_id) {
		$date = date("Y-m-d");
		$sql = "
			select * from
			(
			select DATE_FORMAT(u.waktu,'%h:%i') jam,u.*
			from ukm_panggil u  
			where  u.ukm_id = '$ukm_id' and date(u.waktu) = date('$date') 
			order by u.waktu desc 
			) as data
	      ";
	      $model = Yii::app()->db->createCommand($sql)->queryAll();
	      echo json_encode($model);

		}
		public function actionGetPengajuan($username) {
			$sql = "
	      select DATE_FORMAT(tanggal_input,'%d %b %y %h:%i') tinput ,
	      u.*, icon, k.nama nama_kategori, kel.nama nama_kelurahan, kec.nama nama_kecamatan
	      from ukm u  
	      inner join kategori k  on k.id = u.kategori
	      left join kelurahan kel on kel.id = u.kelurahan
	      left join kecamatan kec on kec.id = kel.kecamatan_id

	       where  u.rekomender = '$username'
	       order by tanggal_input desc

	      ";
	      // echo $sql;
	      $model = Yii::app()->db->createCommand($sql)->queryAll();
	      echo json_encode($model);

		}
		public function actionCariById($id) {


	      $sql = "
	      select 
	      u.*, icon, k.nama nama_kategori, kel.nama nama_kelurahan, kec.nama nama_kecamatan,delivery
	      from ukm u  
	      inner join kategori k  on k.id = u.kategori
	      left join kelurahan kel on kel.id = u.kelurahan
	      left join kecamatan kec on kec.id = kel.kecamatan_id

	       where  u.id = $id

	      ";
	      $sql_jadwal = "select * from ukm_operasional where ukm_id = $id ";
	      $sql_komentar = "select * from ukm_komentar where ukm_id = $id and status_publish = 1 order by datetime desc limit 5";
	      $sql_produk = "select * from produk where ukm_id = $id";
	      $sql_loker = "select * from ukm_loker where ukm_id = $id";
		  $sql_rate = "select sum(rate) as rate, count(*) as jumlah from ukm_rate where ukm_id = '$id' order by created_date desc  ";			


		  $rate = Yii::app()->db->createCommand($sql_rate)->queryRow();
	      $model = Yii::app()->db->createCommand($sql)->queryRow();
	      $jadwal = Yii::app()->db->createCommand($sql_jadwal)->queryAll();
	      $produk = Yii::app()->db->createCommand($sql_produk)->queryAll();
	      $loker = Yii::app()->db->createCommand($sql_loker)->queryAll();
	      $komentar = Yii::app()->db->createCommand($sql_komentar)->queryAll();

	      $array = array();
	      $array["model"] = $model;
	      $array["jadwal"] = $jadwal;
	      $array["produk"] = $produk;
	      $array["loker"] = $loker;
	      $array["komentar"] = $komentar;

	      	$jml = 0;
			if ($rate['jumlah']==0){
				$jml = 1;
			}else{
				$jml = $rate['jumlah'];
			}
			$rate = round($rate['rate']/$jml,1);
			$x = array("rate"=>$rate);
			// echo json_encode($array);

	      $array["rate"] = $x;

	      echo json_encode($array);

		}
		public function actionCariCustom($status,$lat,$lon) {
			$time = date("H:i");
		  	$date = date("Y-m-d");
		  	$sql_adt = "
				 IF (u.tipe=3,'-',
					(CASE WHEN time('$time') <= jam_tutup AND time('$time') >= jam_buka THEN 'Sedang Buka'  
					ELSE 'Tutup' 
					END 
					)
				) AS status_buka,";

		  	 // if(time('$time')<jam_tutup,'buka','tutup') as status_buka,
	     //  	  CASE 
	     //      WHEN time('$time')<=jam_tutup AND time('$time')>=jam_buka THEN 'Sedang Buka' 
	     //      ELSE 'Tutup'
	     //  	  END AS status_buka,";
	      $sql_near = " round( 6371 * ACOS( COS( RADIANS( $lat ) ) * COS( RADIANS( if (u.tipe='3',au.last_lat,u.lat) ) ) * COS( RADIANS( if (u.tipe='3',au.last_lng,u.lon) ) - RADIANS( $lon) ) + SIN( RADIANS( $lat ) ) * SIN( RADIANS( if (u.tipe='3',au.last_lat,u.lat) ) ) ) ,2) AS distance,";

		  if ($status=="favorite"){
		      $sql = "
		    	select 	$sql_adt $sql_near

		    	count(uf.id) jumlah ,

				u.nama nama,
				if (u.tipe='3','Posisi Tidak Tetap',alamat) as alamat,
				if (u.tipe='3',au.last_lat,u.lat) as lat,
				if (u.tipe='3',au.last_lng,u.lon) as lon,			
				u.keterangan keterangan,
				u.jam jam,
				u.rt,
				u.rw,
				u.subkategori,
				u.tipe tipe,
				u.tanggal_input,
				u.tanggal_akhir,
				u.kelurahan ,
				u.pemilik pemilik,
				u.telepon telepon,
				u.rekomender rekomender,
				u.username username,
				u.kategori kategori_id,
				u.gambar,
				u.kendaraan kendaraan 
		    	

		    	,k.nama nama_kategori from 
		    	ukm u 
		    	inner join ukm_favorite uf 	
		    	on uf.ukm_id = u.id
			    inner join kategori k 
			    on k.id = u.kategori 
			    inner join ukm_operasional uo on uo.ukm_id = u.id
		    	left join aset_user au on au.username = u.telepon


				group by u.id
		    	having jumlah > 0  and distance <=1

		    	order by jumlah desc
		      ";
		  }else if ($status=="tenda"){
		  	 $sql = "
		    	select $sql_adt	 $sql_near

		    		
		    	u.nama nama,	
				if (u.tipe='3','Posisi Tidak Tetap',alamat) as alamat,
				if (u.tipe='3',au.last_lat,u.lat) as lat,
				if (u.tipe='3',au.last_lng,u.lon) as lon,			
				u.keterangan keterangan,
				u.jam jam,
				u.rt,
				u.rw,
				u.subkategori,
				u.tipe tipe,
				u.tanggal_input,
				u.tanggal_akhir,
				u.kelurahan ,
				u.pemilik pemilik,
				u.telepon telepon,
				u.rekomender rekomender,
				u.username username,
				u.kategori kategori_id,
				u.gambar,
				u.kendaraan kendaraan,

		    	k.nama nama_kategori from 
		    	ukm u 
			    inner join kategori k 
			    on k.id = u.kategori 
			    inner join ukm_operasional uo on uo.ukm_id = u.id
		    	left join aset_user au on au.username = u.telepon

			     where u.tipe = 2
			     group by u.id
			     having    distance <=1
		    	
		      ";
		  
		   }else if ($status=="bangunan"){
		  	 $sql = "
		    	select 	$sql_adt $sql_near
		    	u.nama nama,	
				if (u.tipe='3','Posisi Tidak Tetap',alamat) as alamat,
				if (u.tipe='3',au.last_lat,u.lat) as lat,
				if (u.tipe='3',au.last_lng,u.lon) as lon,			
				u.keterangan keterangan,
				u.jam jam,
				u.rt,
				u.rw,
				u.subkategori,
				u.tipe tipe,
				u.tanggal_input,
				u.tanggal_akhir,
				u.kelurahan ,
				u.pemilik pemilik,
				u.telepon telepon,
				u.rekomender rekomender,
				u.username username,
				u.kategori kategori_id,
				u.gambar,
				u.kendaraan kendaraan, 
				k.nama nama_kategori from 
		    	ukm u 
			    inner join kategori k 
			    on k.id = u.kategori 
			    inner join ukm_operasional uo on uo.ukm_id = u.id
		    	left join aset_user au on au.username = u.telepon

			     where u.tipe = 1
			     group by u.id
			     having    distance <=1
		    	
		      ";
	     }else if ($status=="keliling"){
		  	 $sql = "
		    	select 	$sql_adt $sql_near
		    		
				if (u.tipe='3','Posisi Tidak Tetap',alamat) as alamat,
				if (u.tipe='3',au.last_lat,u.lat) as lat,
				if (u.tipe='3',au.last_lng,u.lon) as lon,			
				u.keterangan keterangan,
				u.nama nama,
				u.jam jam,
				u.rt,
				u.rw,
				u.subkategori,
				u.tipe tipe,
				u.tanggal_input,
				u.tanggal_akhir,
				u.kelurahan ,
				u.pemilik pemilik,
				u.telepon telepon,
				u.rekomender rekomender,
				u.username username,
				u.kategori kategori_id,
				u.gambar,
				u.kendaraan kendaraan , k.nama nama_kategori from 
		    	ukm u 
			    inner join kategori k on k.id = u.kategori 
			    inner join ukm_operasional uo on uo.ukm_id = u.id
			    left join aset_user au on au.username = u.telepon
			    where u.tipe = 3 and au.isSigning =1
			  	
			    group by u.id	 
			    having    distance <=1   	
		      ";
	 
		    }else if ($status=="dekat"){
		  	 $sql = "
		    	select 	$sql_adt $sql_near

	    		u.nama nama,
				if (u.tipe='3','Posisi Tidak Tetap',alamat) as alamat,
				if (u.tipe='3',au.last_lat,u.lat) as lat,
				if (u.tipe='3',au.last_lng,u.lon) as lon,			
				u.keterangan keterangan,
				u.jam jam,
				u.rt,
				u.rw,
				u.subkategori,
				u.tipe tipe,
				u.tanggal_input,
				u.tanggal_akhir,
				u.kelurahan ,
				u.pemilik pemilik,
				u.telepon telepon,
				u.rekomender rekomender,
				u.username username,
				u.kategori kategori_id,
				u.gambar,
				u.kendaraan kendaraan 
		    	, k.nama nama_kategori from 
		    	ukm u 
			    inner join kategori k 
			    on k.id = u.kategori 
			    inner join ukm_operasional uo on uo.ukm_id = u.id
		    	left join aset_user au on au.username = u.telepon

				group by u.id
			     HAVING distance <=1
			     order by distance asc

		      ";
		  }
		    else if ($status=="baru"){
		  	 $sql = "
		    	select 	$sql_adt $sql_near

		    	u.nama nama,	
				if (u.tipe='3','Posisi Tidak Tetap',alamat) as alamat,
				if (u.tipe='3',au.last_lat,u.lat) as lat,
				if (u.tipe='3',au.last_lng,u.lon) as lon,			
				u.keterangan keterangan,
				u.jam jam,
				u.rt,
				u.rw,
				u.subkategori,
				u.tipe tipe,
				u.tanggal_input,
				u.tanggal_akhir,
				u.kelurahan ,
				u.pemilik pemilik,
				u.telepon telepon,
				u.rekomender rekomender,
				u.username username,
				u.kategori kategori_id,
				u.gambar,
				u.kendaraan kendaraan 
		    	, k.nama nama_kategori from 
		    	ukm u 
			    inner join kategori k 
			    on k.id = u.kategori 
			    inner join ukm_operasional uo on uo.ukm_id = u.id
		    	left join aset_user au on au.username = u.telepon
				where
				date(tanggal_input) <= date(now())
				and 
				date(tanggal_input) >= (date(NOW()) - INTERVAL 7 DAY)

			     
				group by u.id
				having distance <=1
				order by u.id desc

		      ";
		  }else if ($status=="buka"){
		  	
		  	 $sql = "


                select 
                $sql_adt

                DAYOFWEEK( date('$date') ), time(uo.jam_tutup), jam_tutup, $sql_near

				date_format(now(),'%k:%i') jam,uo.jam_buka, uo.jam_tutup ,


				if (u.tipe='3','Posisi Tidak Tetap',alamat) as alamat,
				if (u.tipe='3',au.last_lat,u.lat) as lat,
				if (u.tipe='3',au.last_lng,u.lon) as lon,		
				u.nama nama,	
				u.keterangan keterangan,
				u.jam jam,
				u.rt,
				u.rw,
				u.subkategori,
				u.tipe tipe,
				u.tanggal_input,
				u.tanggal_akhir,
				u.kelurahan ,
				u.pemilik pemilik,
				u.telepon telepon,
				u.rekomender rekomender,
				u.username username,
				u.kategori kategori_id,
				u.gambar,
				u.kendaraan kendaraan 
				, k.nama nama_kategori from 
				ukm u 
				inner join kategori k 
				on k.id = u.kategori
				inner join ukm_operasional uo on uo.ukm_id = u.id
				left join aset_user au on au.username = u.telepon

				where uo.hari = DAYOFWEEK( date('$date') )
				and
				time('$time') >= time(uo.jam_buka) 
				and 
				time('$time') <= time(uo.jam_tutup) 
				having distance <=1


		      ";
		  }


	          $model = Yii::app()->db->createCommand($sql)->queryAll();
	  	
	          echo json_encode($model);
	      
		}
		public function actionCari() {
			$time = date("H:i");
		  	$date = date("Y-m-d");
			header('Access-Control-Allow-Origin: *');
			$kel = $_REQUEST['kelurahan'];
			$rt = $_REQUEST['rt'];
			$rw = $_REQUEST['rw'];
			$lat = $_REQUEST['lat'];
			$keyw = $_REQUEST['kata_kunci'];
			$jenisusaha = $_REQUEST['jenisusaha'];
			$lon = $_REQUEST['lon'];
			$isnear = $_REQUEST['isnear'];
			$tipe = $_REQUEST['tipe'];
			$username = $_REQUEST['username'];


		


			$filterdata = array(
				"kelurahan"=>$kel,
				"rt"=>$rt,
				"rw"=>$rw,
				"rw"=>$rw,
				"lat"=>$lat,
				"keyw"=>$keyw,
				"jenisusaha"=>$jenisusaha,
				"lon"=>$lon,
				"isnear"=>$isnear
			);

			

			// $sql_string = " and uo.hari = DAYOFWEEK( date('$date') )";
			$sql_string = " ";

			// if ($username!="null"){
			// 	$sql_string .= " and kategori in (select id from kategori where nama in (select jenis from user_jenis_favorite where username = '$username') )";
			// }

		

		  if (isset($jenisusaha)){
	          if (count($jenisusaha) !=0 ){
	            $sql_j = "";        

	            foreach ($jenisusaha as $key => $value) {
	              $sql_j .= $value.",";
	            }

	            $sql_j = rtrim($sql_j,",");
	            
	              $sql_string .= " and  k.id in ($sql_j) ";
	          }
	      }

	      if (!empty($tipe) ) {
            $sql_string .= " and u.tipe = $tipe ";
            // echo "masuk";
	      }

	       if (isset($keyw)){
	            $sql_string .= " and (u.nama like  '%$keyw%' || k.nama like  '%$keyw%' || pd.nama like '%$keyw%')  ";
	      }

		  if ($isnear){
	      	// $sql_near = " round( 6371 * ACOS( COS( RADIANS( $lat ) ) * COS( RADIANS( lat ) ) * COS( RADIANS( lon ) - RADIANS( $lon) ) + SIN( RADIANS( $lat ) ) * SIN( RADIANS( lat ) ) ) ,2) AS distance,";
	      	$sql_near = " round( 6371 * ACOS( COS( RADIANS( $lat ) ) * COS( RADIANS( if (u.tipe='3',au.last_lat,u.lat) ) ) * COS( RADIANS( if (u.tipe='3',au.last_lng,u.lon) ) - RADIANS( $lon) ) + SIN( RADIANS( $lat ) ) * SIN( RADIANS( if (u.tipe='3',au.last_lat,u.lat) ) ) ) ,2) AS distance,";
	      	$last_near = " group by u.id  HAVING distance <=1 ORDER BY distance ";
	      	$where  = " where u.isaktif=1   ";


	      }else{
	      	$where  = " where u.isaktif=1    ";
	      	$sql_near = " round( 6371 * ACOS( COS( RADIANS( $lat ) ) * COS( RADIANS( if (u.tipe='3',au.last_lat,u.lat) ) ) * COS( RADIANS( if (u.tipe='3',au.last_lng,u.lon) ) - RADIANS( $lon) ) + SIN( RADIANS( $lat ) ) * SIN( RADIANS( if (u.tipe='3',au.last_lat,u.lat) ) ) ) ,2) AS distance,";
	      	$last_near = " group by u.id ORDER BY distance asc";
	      

		

		  if (isset($kel) && $kel!=''){	            
              $sql_string .= " and kelurahan = $kel ";
	      }

	      


	     
	   
	      if (isset($rw) && $rw!=''){   
          	$sql_string .= " and rw  = $rw "; 
	      }

         if (isset($rt) && $rt!=''){
	          if (count($rt) !=0 ){
	            $sql_t = "";        

	            foreach ($rt as $key => $value) {
	              $sql_t .= $value.",";
	            }
	            $sql_t = rtrim($sql_t,",");
	            
	              $sql_string .= " and rt in ($sql_t) ";
	          }else{
	          
	          }
	      }



	    
	      }

	      $where .= "
		AND
	      (
	      CASE WHEN (u.tipe='3') THEN
	      	 au.isSigning = 1	
	      ELSE
	        au.isSigning = 0 || au.isSigning = 1
	      END
	      )
	      ";

	      if (isset($_REQUEST['tipe_hard'])){
	      	$where .= " and u.tipe = 3";
	      }

		// IF (tipe=3,'-',if(time('$time') < jam_tutup,'buka','tutup') )as status_buka,
	    
	     // echo "<pre>";
	     // print_r($_REQUEST);
	     // echo "</pre>";

	

		$sql = "
		select 
		isSigning,
		if (date(u.tanggal_akhir) < date('$date'),'1',0) verif,
		
		
		 IF (u.tipe=3,'-',
			(CASE WHEN time('$time') <= jam_tutup AND time('$time') >= jam_buka THEN 'Sedang Buka'  
			ELSE 'Tutup' 
			END 
			)
		) AS status_buka,

		uo.jam_buka,
		uo.jam_tutup,
		u.id,
		u.nama nama ,
		if (u.tipe='3','Posisi Tidak Tetap',alamat) as alamat,
		
		if (u.tipe='3',au.last_lat,u.lat) as lat,
		if (u.tipe='3',au.last_lng,u.lon) as lon,			
		
		u.keterangan keterangan,
		u.jam jam,
		u.rt,
		u.rw,
		u.subkategori,
		u.tipe tipe,
		u.tanggal_input,
		u.tanggal_akhir,
		u.kelurahan ,
		u.pemilik pemilik,
		u.telepon telepon,
		u.rekomender rekomender,
		u.username username,
		u.kategori kategori_id,
		u.gambar,
		u.kendaraan kendaraan ,
		icon, 
		$sql_near
		k.nama nama_kategori
		
		from ukm u  
		left join kategori k on k.id = u.kategori
		left join ukm_operasional uo on uo.ukm_id = u.id
		left join produk pd on pd.ukm_id = u.id
		left join aset_user au on au.username = u.telepon

		$where 

		$sql_string 
		$sql_kel
		$last_near

		";
		
	      $model = Yii::app()->db->createCommand($sql)->queryAll();
	      $data = array(
	      	"model"=>$model,
	      	"filterdata"=>$filterdata
      	  );

	      echo json_encode($data);

		}
		public function actionGetjenisusaha() {
			$data = array();
			// $model = Kategori::model()->findAll();
			// echo json_encode($model->getAttributes(array("id","nama"));
			$sql = "select * from kategori order by nama asc";
			$model = Yii::app()->db->createCommand($sql)->queryAll();
			$data["kategori"] = $model;

			$sql = "select * from kecamatan ";
			$model = Yii::app()->db->createCommand($sql)->queryAll();
			$data["kecamatan"] = $model;

			$sql = "select * from kelurahan order by kecamatan_id asc ";
			$model = Yii::app()->db->createCommand($sql)->queryAll();
			$data["kelurahan"] = $model;

			echo json_encode($data);





			// echo "<pre>";
			// print_r($model);
			// echo "</pre>";

			// foreach ($model as $key => $value) {
			// 	echo "<option value='$value->id'>$value->nama</option>";
			// }
		}
		
		// public function actionFindByPrimaryKey($primaryKey) {
		// 	$student = Student::Model()->findByPk($primaryKey);
				
		// 	if ($student) {
		// 		echo "data ditemukan, NRPnya {$student->code} dan nama nya {$student->name} ";	
		// 	}else {
		// 		echo "data tidak di temkan ";
		// 	}	
		// }	
		
		// public function actionFindByAttributes() {
		// 	$student = Student::Model()->findByAttributes(array('code'=>'3313002'));
				
		// 	if ($student) {
		// 		echo "data ditemukan, NRPnya {$student->code} dan nama nya {$student->name} ";	
		// 	}else {
		// 		echo "data tidak di temkan ";
		// 	}	
		// }	
		
		// public function actionFindAll() {
		// 	$students = Student::Model()->findAll();
				
		// 	if ($students) {
		// 		foreach ($students as $student) {
		// 			echo "Nama : {$student->name} NRP : {$student->code} <br/>" ;
		// 			}
		// 	}else {
		// 		echo "data tidak di temkan ";
		// 	}	
		// }
		
		// public function actionFindAllByPrimaryKeys() {
		// 	$primaryKeys = array ( 1,8,4);
		// 	$students = Student::Model()->findAllByPk($primaryKeys);
				
		// 	if ($students) {
		// 		foreach ($students as $student) {
		// 			echo "Nama : {$student->name} NRP : {$student->code} <br/>" ;
		// 			}
		// 	}else {
		// 		echo "data tidak di temkan ";
		// 	}	
		// }		
		
		// public function actionFindAllByAttributes() {
		// 	$attributes = array ( 
		// 		'name'=>array(
		// 			'zulfa',
		// 			'putri',
		// 			'dede',
		// 		)	
		// 	);
		// 	$students = Student::Model()->findAllByAttributes($attributes);
				
		// 	if ($students) {
		// 		foreach ($students as $student) {
		// 			echo "Nama : {$student->name} NRP : {$student->code} <br/>" ;
		// 			}
		// 	}else {
		// 		echo "data tidak di temkan ";
		// 	}	
		// }	

		// public function actionaddCondition() {
		// 	$criteria = new CDbCriteria(); 
		// 	$criteria->addCondition('code = :code');
		// 	$criteria->addCondition('name = :name');
		// 	$criteria->params = array(
		// 		'code'=>'3313002',
		// 		'name'=>'Saeful rohman',
		// 	);
		// 	$student = Student::Model()->find($criteria);
				
		// 	if ($student) {
		// 		echo "data ditemukan, NRPnya {$student->code} dan nama nya {$student->name} ";	
		// 	}else {
		// 		echo "data tidak di temkan ";
		// 	}	
		// }	
		
		// public function actionAddInCondition() {
		// 	$criteria = new CDbCriteria(); 
		// 	$criteria->addInCondition('code', array('3313002','3313027','3313143'));
		// 	$students = Student::Model()->findAll($criteria);
				
		// 	if ($students) {
		// 		foreach ($students as $student) {
		// 			echo "Nama : {$student->name} NRP : {$student->code} <br/>" ;
		// 			}
		// 	}else {
		// 		echo "data tidak di temkan ";
		// 	}	
		// }
		
		// public function actionAddBetweenCondition() {
		// 	$criteria = new CDbCriteria(); 
		// 	$criteria->addBetweenCondition('code', '3313002','3313027');
		// 	$criteria->order = 'code DESC';
		// 	$students = Student::Model()->findAll($criteria);
				
		// 	if ($students) {
		// 		foreach ($students as $student) {
		// 			echo "Nama : {$student->name} NRP : {$student->code} <br/>" ;
		// 			}
		// 	}else {
		// 		echo "data tidak di temkan ";
		// 	}	
		// }	
		
		// public function actionFindAllToForm(){
		// 	$students = Student::Model()->findAll();
		// 	$this->render('findAllToForm', array ('students'=>$students)); 
				
		// }		
		
		// public function actionAngka () {
		// 	$numbers= array (1, 2, 3, 4, 5) ;
		// 	if ($numbers) {
		// 		foreach ($numbers as $number) {
		// 			echo "{$number} </br>";					
		// 			}
		// 	}else {
		// 		echo "data tidak di temkan ";
		// 	}	
		// }	
			
		// public function actionMobil () {
		// 	$cars= array (
		// 			array(
		// 				'name'=>'A',	
		// 				'color'=>'red',
		// 				'brand'=>'BMW',
		// 				),
		// 			array(
		// 				'name'=>'B',	
		// 				'color'=>'Blue',
		// 				'brand'=>'Mercy',
		// 				),	
		// 			) ;
					
				
		// 		foreach ($cars as $car) {
		// 			foreach ($car as $detail) {
		// 				echo " {$detail}</br>" ;	
		// 			}
		// 		}
			
		// 	}	

		// public function actionMobil2 () {
		// 	$cars= array (
		// 			array(
		// 				'name'=>'A',	
		// 				'color'=>'red',
		// 				'brand'=>'BMW',
		// 				),
		// 			array(
		// 				'name'=>'B',	
		// 				'color'=>'Blue',
		// 				'brand'=>'Mercy',
		// 				),	
		// 			) ;
					
		// 		foreach ($cars as $car) {	
		// 			echo "{$car['name']},{$car['color']},{$car['brand']}";
		// 		}
		// 	}
				
}			
	