<?php

// 共通変数・関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー情報登録・編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ====================
// 画面処理
// ====================

// 画面表示用データ取得

// GETデータを格納
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id']:'';
// DBからユーザー情報を取得
$dbFormData = (!empty($u_id)) ? getUserOne($u_id):'';
// 新規登録 or 編集 判別フラグ
$edit_flg = (empty($dbFormData)) ? false:true;
debug('ユーザーID：'.$u_id);
debug('フォーム用DBデータ：'.print_r($dbFormData,true));

// POST送信時処理
if (!empty($_POST)) {
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));

  // 変数にユーザー情報を代入
  $name = $_POST['name'];
  $email = $_POST['email'];
  $tel = $_POST['tel'];
  $zip = $_POST['zip'];
  $addr = $_POST['addr'];
  $birth_y = $_POST['birth_y'];
  $birth_m = $_POST['birth_m'];
  $birth_d = $_POST['birth_d'];
  $age = $_POST['age'];
  $gender = $_POST['gender'];
  $comment = $_POST['comment'];
  // 画像をアップロードしてパスを格納
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic'):'';
  // 画像をPOSTしていないがすでにDBに登録されている場合はDBのパスを入れる
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic']:$pic;

  // 未入力チェック
  validRequired($name,'name');
  validRequired($email,'email');
  validRequired($tel,'tel');
  validRequired($zip,'zip');
  validRequired($addr,'addr');
  validRequired($age,'age');
  validRequired($gender,'gender');

  if (empty($err_msg)) {
    if (empty($dbFormData)) { // 更新の場合はDBの情報と異なる部分についてバリデーションを行う
      validMaxLen($name,'name');
      validEmail($email,'email');
      validMaxLen($email,'email');
      validEmailDup($email,'email');
      validTel($tel,'tel');
      validNumber($tel,'tel');
      validZip($zip,'zip');
      validNumber($zip,'zip');
      validMaxLen($addr,'addr');
      validNumber($age,'age');
      validMaxLen($comment,'comment',500);
    } else {
      if ($dbFormData['name'] !== $name) {
        validRequired($name,'name');
        validMaxLen($name,'name');
      }
      if ($dbFormData['email'] !== $email) {
        validRequired($email,'email');
        validEmail($email,'email');
        validMaxLen($email,'email');
        validEmailDup($email,'email');
      }
      if ($dbFormData['tel'] !== $tel) {
        validRequired($tel,'tel');
        validTel($tel,'tel');
        validNumber($tel,'tel');
      }
      if ($dbFormData['zip'] !== $zip) {
        validRequired($zip,'zip');
        validZip($zip,'zip');
        validNumber($zip,'zip');
      }
      if ($dbFormData['addr'] !== $addr) {
        validRequired($addr,'addr');
        validMaxLen($addr,'addr');
      }
      if ($dbFormData['age'] !== $age) {
        validRequired($age,'age');
        validNumber($age,'age');
      }
      if ($dbFormData['comment'] !== $comment) {
        validMaxLen($comment,'comment',500);
      }
    }
  }

  if (empty($err_msg)) {
    debug('バリデーションOK');

    // 例外処理
    try {
      // 接続
      $dbh = dbConnect();
      // 編集画面の場合はUPDATEL文、新規登録画面の場合はINSERT文を生成
      if ($edit_flg) {
        debug('DB更新');
        $sql = 'UPDATE users SET name = :name, email = :email, tel = :tel, zip = :zip, addr = :addr, birth_y = :birth_y, birth_m = :birth_m, birth_d = :birth_d, age = :age, gender = :gender, comment = :comment, pic = :pic WHERE id = :u_id AND delete_flg = 0';
        $data = array(':name' => $name, ':email' => $email, ':tel' => $tel, ':zip' => $zip, ':addr' => $addr, ':birth_y' => $birth_y, ':birth_m' => $birth_m, ':birth_d' => $birth_d, ':age' => $age, ':gender' => $gender, ':comment' => $comment, ':pic' => $pic, ':u_id' => $dbFormData['id']);
      } else {
        debug('DB新規登録');
        $sql = 'INSERT INTO users (name, email, tel, zip, addr, birth_y, birth_m, birth_d, age, gender, comment, pic, create_date) VALUES (:name, :email, :tel, :zip, :addr, :birth_y, :birth_m, :birth_d, :age, :gender, :comment, :pic, :date)';
        $data = array(':name' => $name, ':email' => $email, ':tel' => $tel, ':zip' => $zip, ':addr' => $addr, ':birth_y' => $birth_y, ':birth_m' => $birth_m, ':birth_d' => $birth_d, ':age' => $age, ':gender' => $gender, ':comment' => $comment, ':pic' => $pic, ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL：'.$sql);
      debug('流し込みデータ：'.print_r($data,true));

      // クエリ実行
      $stmt = querypost($dbh,$sql,$data);

      // クエリ実行結果から１レコード返却
      if ($stmt) {
        debug('一覧画面に遷移');
        header("Location:index.php");
      }

    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG06;
    }
  }
}
debug('画面表示処理終了|<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

 ?>
 <?php
$siteTitle = (!$edit_flg) ? 'ユーザー情報：新規登録':'ユーザー情報：編集';
require('head.php');
 ?>

  <body class="page-create">

    <!-- ヘッダー -->
    <header>
      <div class="site-width">
        <h1><?php echo (!$edit_flg) ? '管理画面｜登録':'管理画面｜編集'; ?></h1>
        <nav id="top-nav">
          <ul>
            <li><a href="index.php">一覧画面</a></li>
            <li><a href="#">○○する</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width" style="padding-bottom:20px;">
      <h1 class="page-title"><?php echo (!$edit_flg) ? '新規登録':'編集'; ?></h1>

      <!-- メイン -->
        <section id="main">
          <div class="form-container">
            <form action="" method="post" class="form" enctype="multipart/form-data" style="margin-bottom:20px;padding-bottom:0;">
              <div class="area-msg">
                <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
              </div>
              <label class="<?php if (!empty($err_msg['name'])) echo 'err' ?>">
                氏名
                <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['name'])) echo $err_msg['name']; ?>
              </div>
              <label class="<?php if (!empty($err_msg['email'])) echo 'err' ?>">
                メールアドレス
                <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?>
              </div>
              <label class="<?php if (!empty($err_msg['tel'])) echo 'err' ?>">
                電話番号 (※ハイフン無し)
                <input type="text" name="tel" value="<?php echo getFormData('tel'); ?>">
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['tel'])) echo $err_msg['tel']; ?>
              </div>
              <label class="<?php if (!empty($err_msg['zip'])) echo 'err' ?>">
                郵便番号 (※ハイフン無し)
                <input type="text" name="zip" value="<?php echo getFormData('zip'); ?>">
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['zip'])) echo $err_msg['zip']; ?>
              </div>
              <label class="<?php if (!empty($err_msg['addr'])) echo 'err' ?>">
                住所
                <input type="text" name="addr" value="<?php echo getFormData('addr'); ?>">
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['addr'])) echo $err_msg['addr']; ?>
              </div>
              <label class="<?php if (!empty($err_msg['birthday'])) echo 'err' ?>">
                生年月日
               <div class="birth">
                <select name="birth_y">
                  <option value="<?php echo getFormData('birth_y'); ?>"><?php echo (!empty(getFormData('birth_y'))) ? getFormData('birth_y'):'--'; ?></option>
                  <?php foreach(range(1920,2016) as $year): ?>
                  <option value="<?= $year ?>"><?= $year ?></option>
                  <?php endforeach; ?>
                </select>
                <select name="birth_m">
                  <option value="<?php echo getFormData('birth_m'); ?>"><?php echo (!empty(getFormData('birth_m'))) ? getFormData('birth_m'):'--'; ?></option>
                  <?php foreach(range(1,12) as $month): ?>
                  <option value="<?= str_pad($month,2,0,STR_PAD_LEFT) ?>"><?= $month ?></option>
                  <?php endforeach; ?>
                </select>
                <select name="birth_d">
                  <option value="<?php echo getFormData('birth_d'); ?>"><?php echo (!empty(getFormData('birth_d'))) ? getFormData('birth_d'):'--'; ?></option>
                  <?php foreach(range(1,31) as $day): ?>
                  <option value="<?= str_pad($day,2,0,STR_PAD_LEFT) ?>"><?= $day ?></option>
                  <?php endforeach; ?>
                </select>
               </div>
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['birthday'])) echo $err_msg['birthday']; ?>
              </div>
              <label class="<?php if (!empty($err_msg['age'])) echo 'err' ?>">
                年齢
                <input type="text" name="age" value="<?php echo getFormData('age'); ?>">
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['age'])) echo $err_msg['age']; ?>
              </div>
              <label class="<?php if (!empty($err_msg['gender'])) echo 'err' ?>">
                性別
                <input type="radio" name="gender" value="male">男性
                <input type="radio" name="gender" value="female">女性
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['gender'])) echo $err_msg['gender']; ?>
              </div>
              <label class="<?php if (!empty($err_msg['comment'])) echo 'err' ?>">
                コメント入力
                <textarea name="comment" id="js-count" rows="10" cols="50"><?php echo getFormData('comment'); ?></textarea>
              </label>
              <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>
              <div class="area-msg">
                <?php if (!empty($err_msg['comment'])) echo $err_msg['comment']; ?>
              </div>
              プロフィール画像
              <label class="area-drop <?php if (!empty($err_msg['pic'])) echo 'err'; ?>" style="height:370px;line-height:370px;">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic" class="input-file" style="height:370px;">
                <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic'))) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php if (!empty($err_msg['pic'])) echo $err_msg['pic']; ?>
              </div>
              <div class="btn-container">
                <input type="submit" name="btn btn-mid" value="<?php echo (!$edit_flg) ? '登 録':'更 新'; ?>" class="register">
              </div>
            </form>
          </div>
        </section>

    </div>

<?php
  require('footer.php');
 ?>
