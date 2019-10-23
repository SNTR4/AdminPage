<?php

// 共通変数・関数ファイル読込
require('function.php');

debug('||||||||||||||||||||||||||||||||||||||||');
debug('詳細ページ');
debug('||||||||||||||||||||||||||||||||||||||||');
debugLogStart();

// ====================
// 画面処理
// ====================

// 画面表示用データ取得
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id']:'';
// DBからユーザー情報を取得
$viewData = getUserOne($u_id);
// パラメータに不正な値が入っていないかチェック
if (empty($viewData)) {
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:index.php");
}
debug('取得したDBデータ：'.print_r($viewData,true));

// POST送信されていた場合
if (!empty($_POST['submit'])) {
  debug('POST送信があります');

  // 例外処理
  try {
    // 接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'UPDATE users SET st_id = 2, delete_flg = 1 WHERE id = :u_id';
    $data = array(':u_id' => $viewData['id']);
    // クエリ実行
    $stmt = querypost($dbh,$sql,$data);

    // クエリ実行結果から１レコード返却
    if ($stmt) {
      debug('クエリ成功');
      header("Location:index.php");
    } else {
      debug('クエリ失敗');
      $err_msg['common'] = MSG06;
    }

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG06;
  }
}
debug('画面表示処理終了|<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

 ?>
<?php
$siteTitle = 'ユーザー情報：詳細';
require('head.php');
 ?>

  <body class="page-detail">

    <!-- ヘッダー -->
    <header>
      <div class="site-width">
        <h1>管理画面｜詳細</h1>
        <nav id="top-nav">
          <ul>
            <li><a href="index.php">一覧画面</a></li>
            <li><a href="#">○○する</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      <h1 class="page-title">詳細情報</h1>

      <!-- メイン -->
      <section id="main">
        <div class="form-container">
          <div class="img-container">
            <div class="img-main">
              <img src="<?php echo showImg(sanitize($viewData['pic'])); ?>" alt="プロフィール画像">
            </div>
          </div>
          <div class="detail name">
            <p>氏　名　：　<span><?php echo sanitize($viewData['name']); ?></span></p>
          </div>
          <div class="detail email">
            <p>E-mail　：　<span><?php echo sanitize($viewData['email']); ?></span></p>
          </div>
          <div class="detail tel">
            <p>電話番号：　<span><?php echo sanitize($viewData['tel']); ?></span></p>
          </div>
          <div class="detail zip">
            <p>郵便番号：　<span><?php echo sanitize($viewData['zip']); ?></span></p>
          </div>
          <div class="detail addr">
            <p>住　所　：　<span><?php echo sanitize($viewData['addr']); ?></span></p>
          </div>
          <div class="detail birthday">
            <p>生年月日：　<span><?php echo sanitize($viewData['birth_y']).'年 '.sanitize($viewData['birth_m']).'月 '.sanitize($viewData['birth_d']).'日'; ?></span></p>
          </div>
          <div class="detail age">
            <p>年　齢　：　<span><?php echo sanitize($viewData['age']).'歳'; ?></span></p>
          </div>
          <div class="detail gender">
            <p>性　別　：　<span><?php echo ($viewData['gender'] === 'male' ? '男性':'女性'); ?></span></p>
          </div>
          <div class="detail text">
            <p>コメント：　<span><?php echo sanitize($viewData['comment']); ?></span></p>
          </div>
          <div class="btn-container">
            <form class="" action="" method="post">
              <a href="create.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&u_id='.$viewData['id']:'?u_id='.$viewData['id']; ?>">編 集</a>
              <input type="submit" name="submit" value="削 除" class="delete">
            </form>
          </div>
        </div>
      </section>
    </div>

<?php
  require('footer.php');
 ?>
