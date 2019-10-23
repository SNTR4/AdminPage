<?php

// 共通変数・関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　一覧ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ====================
// 画面処理
// ====================

// 画面表示用データ取得

// GETパラメータを取得
// カレントページ(デフォルトは1ページ目)
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p']:1;
// ステータス
$status = (!empty($_GET['st_id'])) ? $_GET['st_id']:'';
// ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort']:'';

// 表示件数
$listspan = 10;
// 現在の表示レコードの先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listspan); // 1ページ目なら(1-1)*10=0、2ページ目なら(2-1)*10=10
// DBからユーザー情報を取得
$dbUserData = getUserList($currentMinNum,$status,$sort);
// DBからステータス情報を取得
$dbStatusData = getStatus();
debug('現在のページ：'.$currentPageNum);

// ユーザー情報が空であればデータが0、またはパラメータが不正であると
// 考えられるので、トップページへ遷移させる
if (empty($dbUserData['data'])) {
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:index.php");
}

debug('画面表示処理終了|<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

 ?>
 <?php
 $siteTitle = 'ユーザー情報：一覧';
require('head.php');
 ?>

  <body class="page-home">

    <!-- ヘッダー -->
    <header>
      <div class="site-width">
        <h1>管理画面｜一覧</h1>
        <nav id="top-nav">
          <ul>
            <li><a href="create.php">登録する</a></li>
            <li><a href="#">○○する</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      <!-- 検索バー -->
        <div id="searchbar">
          <form method="get">
            <div class="search">
              <h1 class="title">ステータス：</h1>
              <div class="selectbox">
                <span class="st_select"></span>
                <select name="st_id">
                  <option value="0" <?php if (getFormData('st_id',true) == 0) {echo 'selected';} ?>>選択してください</option>
                  <?php foreach ($dbStatusData as $key => $val) { ?>
                    <option value="<?php echo $val['id'] ?>"<?php if (getFormData('st_id',true) == $val['id']) {echo 'selected';} ?>><?php echo $val['name'] ?></option>
                  <?php } ?>
                </select>
              </div>
              <h1 class="title">表示順：</h1>
              <div class="selectbox">
                <span class="st_select"></span>
                <select name="sort">
                  <option value="0" <?php if (getFormData('sort',true) == 0) {echo 'selected';} ?>>選択してください</option>
                  <option value="1" <?php if (getFormData('sort',true) == 1) {echo 'selected';} ?>>昇順</option>
                  <option value="2" <?php if (getFormData('sort',true) == 2) {echo 'selected';} ?>>降順</option>
                </select>
              </div>
              <input type="submit" value="実行" class="button">
            </div>
          </form>
        </div>

        <!-- メイン -->
        <section id="main">
          <div class="records_title" style="margin-bottom:10px;">
            <div class="records_left">
              <span class="total_num"><?php echo sanitize($dbUserData['total']); ?></span>件のデータがあります
            </div>
            <div class="records_right">
              <span class="num"><?php echo (!empty($dbUserData['data'])) ? $currentMinNum + 1:0; ?></span> - <span class="num"><?php echo $currentMinNum + count($dbUserData['data']); ?></span>件 / <span class="num"><?php echo sanitize($dbUserData['total']); ?></span>件中
            </div>
          </div>
          <div class="panel_index">
            <p>氏名</p>
            <p>メールアドレス</p>
            <p>登録日時</p>
            <p>更新日時</p>
          </div>
          <div class="panel_list">
            <?php foreach ($dbUserData['data'] as $key => $val) : ?>
            <div class="panel" style="margin-bottom:10px;">
              <p><?php echo sanitize($val['name']); ?></p>
              <p><?php echo sanitize($val['email']); ?></p>
              <p><?php echo sanitize($val['create_date']); ?></p>
              <p><?php echo sanitize($val['update_date']); ?></p>
              <div class="panel_button">
                <a href="detail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&u_id='.$val['id']:'?u_id='.$val['id']; ?>">詳細</a>
                <a href="create.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&u_id='.$val['id']:'?u_id='.$val['id']; ?>">編集</a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- ページネーション -->

          <?php pagination($currentPageNum,$dbUserData['total_page']); ?>

        </section>
    </div>

<?php
  require('footer.php');
 ?>
