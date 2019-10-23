<?php

// ====================
// ログ
// ====================

// ログの取得
ini_set('log_errors','on');
// ログの出力ファイルを指定
ini_set('error_log','php.log');

// ====================
// デバッグ
// ====================

// デバッグフラグ
$debug_flg = true;
// デバッグフラグ関数
function debug($str) {
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ：'.$str);
  }
}

// ====================
// 画面表示開始ログ
// ====================

function debugLogStart() {
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('現在日時タイムスタンプ：'.time());
}

// ====================
// 定数
// ====================

define('MSG01','入力必須です');
define('MSG02','メールアドレスの形式で入力してください');
define('MSG03','半角数字で入力してください');
define('MSG04','8文字以上で入力してください');
define('MSG05','500文字以内で入力してください');
define('MSG06','エラーが発生しました');
define('MSG07','そのメールアドレスはすでに登録されています');
define('MSG08','電話番号の形式が違います');
define('MSG09','郵便番号の形式が違います');
define('MSG10','');

// ====================
// バリデーション
// ====================

// エラーメッセージ格納用の配列
$err_msg = array();

// 未入力チェック
function validRequired($str,$key) {
  if ($str === '') {
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}

// メールアドレスの形式チェック
function validEmail($str,$key) {
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$str)) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

// メールアドレスの重複チェック
function validEmailDup($email) {
  global $err_msg;
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = querypost($dbh,$sql,$data);
    // クエリ実行結果を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // array_shiftで配列の先頭だけを取り出す
    if (!empty(array_shift($result))) {
      $err_msg['email'] = MSG07;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG06;
  }
}

// 最小文字数チェック
function validMinLen($str,$key,$min = 8) {
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}

// 最大文字数チェック
function validMaxLen($str,$key,$max = 255) {
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}

// 半角数字チェック
function validNumber($str,$key) {
  if (!preg_match("/^[0-9]+$/",$str)) {
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

// 電話番号形式チェック
function validTel($str,$key) {
  if (!preg_match("/^(0{1}\d{9,10})$/",$str)) {
    global $err_msg;
    $err_msg[$key] = MSG08;
  }
}

// 郵便番号形式チェック
function validZip($str,$key) {
  if (!preg_match("/^\d{7}$/",$str)) {
    global $err_msg;
    $err_msg[$key] = MSG09;
  }
}

// エラーメッセージ表示
function getErrMsg($key) {
  global $err_msg;
  if (!empty($err_msg[$key])) {
    return $err_msg[$key];
  }
}

// ====================
// データベース
// ====================

// 接続用関数
function dbConnect() {
  // 接続準備
  $dsn = 'mysql:dbname=CRUD;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時にはエラーログを出力するように設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowcountメソッドを使えるようになる
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成(DBへ接続)
  $dbh = new PDO($dsn,$user,$password,$options);
  return $dbh;
}

// SQL実行関数
function querypost($dbh,$sql,$data) {
  // クエリ作成
  $stmt = $dbh->prepare($sql);
  // プレースホルダに値をセットし、SQL文を実行
  if (!$stmt->execute($data)) {
    debug('クエリ失敗');
    debug('SQLエラー：'.print_r($stmt,true));
    $err_msg['common'] = MSG06;
    return 0;
  }
    debug('クエリ成功');
    return $stmt;
}

// ユーザー情報(一覧)取得用関数
function getUserList($currentMinNum = 1,$status,$sort,$span = 10) {
  debug('ユーザー情報(一覧)を取得します');
  // 例外処理
  try {
    // 接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM users';
    if (!empty($status)) $sql .=' WHERE `st_id` = '.$status;
    if (!empty($sort)) {
      switch ($sort) {
        case 1:
          $sql .=' ORDER BY update_date ASC';
          break;
        case 2:
          $sql .=' ORDER BY update_date DESC';
          break;
      }
    }
    $data = array();
    // クエリ実行
    $stmt = querypost($dbh,$sql,$data);
    $rst['total'] = $stmt->rowCount(); // 総レコード数
    $rst['total_page'] = ceil($rst['total'] / $span); // 総ページ数
    if (!$stmt) {
      return false;
    }

    // ページング用のSQL文作成
    $sql = 'SELECT * FROM users';
    if (!empty($status)) $sql .=' WHERE `st_id` = '.$status;
    if (!empty($sort)) {
      switch ($sort) {
        case 1:
          $sql .=' ORDER BY update_date ASC';
          break;
        case 2:
          $sql .=' ORDER BY update_date DESC';
          break;
      }
    }

    $sql .=' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    // クエリ実行
    $stmt = querypost($dbh,$sql,$data);

    // クエリ実行結果から全レコード返却
    if ($stmt) {
      $rst['data'] = $stmt->fetchAll();
      return $rst;
      var_dump($rst);
    } else {
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

// ユーザー情報(詳細)取得用関数
function getUserOne($u_id) {
  debug('ユーザー情報(詳細)を取得します');
  debug('ユーザーID：'.$u_id);
  // 例外処理
  try {
    // 接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users WHERE id = :u_id';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = querypost($dbh,$sql,$data);

    // クエリ実行結果から１レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

// ステータスデータ取得用関数
function getStatus() {
  debug('ステータスデータを取得します');

  // 例外処理
  try {
    // 接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM status';
    $data = array();
    // クエリ実行
    $stmt = querypost($dbh,$sql,$data);

    // クエリ実行結果から全レコード返却
    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

// ====================
// その他
// ====================

// サニタイズ用関数
function sanitize($str) {
  return htmlspecialchars($str,ENT_QUOTES);
}

// フォーム入力内容保持用関数
function getFormData($str,$flg = false) {
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $dbFormData;
  // DBにユーザー情報がある場合
  if (!empty($dbFormData)) {
    // フォームにエラーがある場合
    if (!empty($err_msg[$str])) {
      // POSTにデータがある場合(郵便番号などのフォームには数字や数値の０が入る場合もあるので「isset」を使う)
      if (isset($method[$str])) {
        return sanitize($method[$str]);
      } else { // POSTにデータがない場合はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    } else { // POSTにデータがあり、DBの情報と違う場合(他のフォームでエラーが出ている状態)
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else { // そもそも変更されていない場合
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}

// 画像処理用関数
function uploadImg($file,$key) {
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション($file['error']の値を確認)
      // 配列内には０や１などの数値が入っている
      switch ($file['error']) {
        case UPLOAD_ERR_OK: // OKの場合
          break;
        case UPLOAD_ERR_NO_FILE: // ファイル未選択の場合
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE: // PHP.ini定義の最大サイズを超過した場合
          throw new RuntimeException('ファイルサイズが大きすぎます');
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズを超過した場合
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default: // その他の場合
          throw new RuntimeException('エラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、「MIMEタイプ」を自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      // in_arrayを使うときは必ず第三引数まで指定する(true = 型を比較させる)
      if (!in_array($type,[IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG],true)) {
        throw new RuntimeException('非対応の画像形式です');
      }

      // ファイルデータからSHA-1ハッシュを取得してファイル名を決定、保存する
      // ハッシュ化しておかないとアップロードされたファイル名が重複してしまう可能性があるため
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);

      // ファイル移動時にエラーが発生した場合
      if (!move_uploaded_file($file['tmp_name'],$path)) {
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスの権限を変更する
      chmod($path,0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e) {
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

// 画像表示用関数
function showImg($path) {
  if (empty($path)) {
    return 'img/sample-img.png';
  } else {
    return $path;
  }
}

// ページネーション用関数
function pagination($currentPageNum,$totalPageNum,$link = '') {
  // 総ページ数が5以内の場合は全ページを表示
  if ($totalPageNum <= 5) {
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  } elseif ($currentPageNum <= 3) { // 総ページ数が5を超えていて かつ 現在ページが3以下の場合は1〜5ページを表示
    $minPageNum = 1;
    $maxPageNum = 5;
  } elseif ($currentPageNum >= $totalPageNum - 2) { // 総ページ数が5を超えていて かつ 現在ページが「総ページ数 - 2」以内の場合は総ページ数の後ろから5ページを表示
    $minPageNum = $totalPageNum - 4;
    $maxPageNum = $totalPageNum;
  } else { // それ以外の場合は現在ページとその前後2ページずつを表示
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo  '<div class="pagination">';
    echo '<ul class="pagination-list">';
    if ($currentPageNum != 1) {
      echo '<li class="list-item"><a href="?st_id='.$_GET["st_id"].'&sort='.$_GET["sort"].'&p=1'.$link.'">&lt;</a></li>';
    }
    for ($i = $minPageNum ; $i <= $maxPageNum ; $i++) {
      echo '<li class="list-item ';
      if ($currentPageNum == $i) { echo 'active'; }
        echo '"><a href="?st_id='.$_GET["st_id"].'&sort='.$_GET["sort"].'&p='.$i.$link.'">'.$i.'</a></li>';
    }
      if ($currentPageNum != $maxPageNum) {
        echo '<li class="list-item"><a href="?st_id='.$_GET["st_id"].'&sort='.$_GET["sort"].'&p='.$maxPageNum.$link.'">&gt;</a></li>';
     }
    echo '</ul>';
  echo '</div>';
}

// GETパラメータ付与用関数
function appendGetParam($arr_del_key = array()) {
  if (!empty($_GET)) {
    $str = '?';
    foreach ($_GET as $key => $val) {
      if (!in_array($key,$arr_del_key,true)) { // 「取り除きたいGETパラメータではない場合」にURLに付与するパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str,0,-1,"UTF-8");
    return $str;
  }
}
