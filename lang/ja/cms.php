<?php

return [
    'auth' => [
        'login' => 'ログイン',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'remember_me' => 'ログイン状態を保持する',
        'forgot_password' => 'パスワードをお忘れですか？',
    ],

    'profile' => [
        'profile' => 'プロフィール',
        'setting' => '設定',
        'logout' => 'ログアウト',
    ],

    'sidebar' => [
        'search_placeholder' => '検索...',
        'dashboard' => 'ダッシュボード',
        'logo' => 'ロゴ',

        'categories' => [
            'title' => 'カテゴリー',
            'add_new' => '新規追加',
            'list' => '一覧',
        ],
        'brands' => [
            'title' => 'ブランド',
            'add_new' => '新規追加',
            'list' => '一覧',
        ],
        'products' => [
            'title' => '商品',
            'add_new' => '新規追加',
            'list' => '一覧',
        ],
        'attributes' => [
            'title' => '属性',
            'add_new' => '新規追加',
            'list' => 'リスト',
        ],
        'customers' => [
            'title' => '顧客',
            'list' => '一覧',
        ],
        'vendors' => [
            'title' => 'ベンダー',
            'add_new' => '新規追加',
            'list' => 'リスト',
        ],
        'product_reviews' => [
            'title' => '商品レビュー',
            'list' => '一覧',
        ],
        'banners' => [
            'title' => 'バナー',
            'add_new' => '新規追加',
            'list' => '一覧',
        ],
        'menu' => [
            'title' => 'メニュー',
            'add_new' => '新規追加',
            'list' => '一覧',
        ],
        'menu_items' => [
            'title' => 'メニュー項目',
            'add_new' => '新規追加',
            'list' => '一覧',
        ],
        'social_media_links' => [
            'title' => 'ソーシャルメディアリンク',
            'add_new' => '新規追加',
            'list' => '一覧',
        ],
        'site_settings' => [
            'title' => 'サイト設定',
            'manage' => '設定を管理',
        ],
        'orders' => [
            'title' => '注文',
            'all_orders' => 'すべての注文',
            'pending_orders' => '保留中の注文',
            'completed_orders' => '完了した注文',
        ],
        'pages' => [
            'title' => 'ページ',
            'add_new' => '新規追加',
            'list' => '一覧',
        ],
        'payments' => [
            'title' => '支払い',
            'list' => '一覧',
        ],

        'refunds' => [
            'title' => '返金',
            'list' => '一覧',
        ],

        'payment_gateways' => [
            'title' => '決済ゲートウェイ',
            'list' => '一覧',
        ],
    ],

    'payment_gateways' => [

        // Page Titles
        'title' => '決済ゲートウェイ',
        'edit_title' => '決済ゲートウェイの編集',

        // Table Headings
        'id' => 'ID',
        'name' => '名前',
        'code' => 'コード',
        'status' => 'ステータス',
        'action' => '操作',

        // Status Labels
        'active' => '有効',
        'inactive' => '無効',

        // Delete Modal
        'delete_confirm' => '削除の確認',
        'delete_message' => 'この決済ゲートウェイを削除してもよろしいですか？',
        'cancel' => 'キャンセル',
        'delete' => '削除',

        // Alerts / Notifications
        'success' => '成功',
        'deleted' => '削除されました',
        'delete_error' => '決済ゲートウェイの削除中にエラーが発生しました！',

        // Form Labels
        'gateway_name' => 'ゲートウェイ名',
        'description' => '説明',
        'active_label' => '有効',
        'configurations' => 'ゲートウェイ設定',
        'key_name' => 'キー名',
        'key_value' => 'キー値',
        'environment' => '環境',
        'sandbox' => 'サンドボックス',
        'production' => '本番',
        'encrypted' => '暗号化',
        'unique' => '一意',
        'update_button' => '更新',

        // Fallback
        'not_available' => '該当なし',
    ],

    'refunds' => [

        // Page Titles
        'title' => '返金',
        'details_title' => '返金の詳細',

        // Table Headings
        'id' => 'ID',
        'payment' => '支払い',
        'amount' => '金額',
        'status' => 'ステータス',
        'reason' => '理由',
        'action' => '操作',

        // Status Labels
        'completed' => '完了',
        'pending' => '保留中',
        'failed' => '失敗',
        'status_labels' => [
            'requested' => 'リクエスト済み',
            'approved' => '承認済み',
            'rejected' => '却下',
            'pending' => '保留中',
            'completed' => '完了',
            'failed' => '失敗',
        ],

        // Filters
        'filters_title' => '払い戻しを絞り込み',
        'status_filter_label' => 'ステータス',
        'status_filter_help' => 'このリストを絞り込むには、1 つ以上のステータスを選択してください。',
        'date_from_label' => '開始日',
        'date_to_label' => '終了日',
        'apply_filters' => 'フィルターを適用',
        'reset_filters' => 'フィルターをリセット',

        // Summary Cards
        'summary_total_count' => '払い戻し総数',
        'summary_completed_count' => '完了した払い戻し',
        'summary_total_amount' => '払い戻し総額',

        // Delete Modal
        'delete_confirm' => '削除の確認',
        'delete_message' => 'この返金を削除してもよろしいですか？',
        'cancel' => 'キャンセル',
        'delete' => '削除',

        // Alerts / Notifications
        'success' => '成功',
        'deleted' => '削除済み',
        'delete_error' => '返金の削除中にエラーが発生しました！',

        // Refund Details Page
        'created_at' => '作成日',
        'updated_at' => '更新日',
        'back' => '返金一覧に戻る',

        // Fallback
        'not_available' => '利用不可',
    ],

    'payments' => [

        // Page Titles
        'title' => '支払い',
        'details_title' => '支払いの詳細',

        // Table Headings
        'id' => 'ID',
        'order' => '注文',
        'user' => 'ユーザー',
        'gateway' => '決済ゲートウェイ',
        'amount' => '金額',
        'status' => 'ステータス',
        'transaction' => '取引',
        'action' => '操作',

        // Status Labels
        'completed' => '完了',
        'pending' => '保留中',
        'failed' => '失敗',

        // Delete Modal
        'delete_confirm' => '削除の確認',
        'delete_message' => 'この支払いを削除してもよろしいですか？',
        'cancel' => 'キャンセル',
        'delete' => '削除',

        // Alerts / Notifications
        'success' => '成功',
        'deleted' => '削除されました',
        'delete_error' => '支払いの削除中にエラーが発生しました！',

        // Payment Details Page
        'transaction_id' => '取引ID',
        'created_at' => '作成日時',
        'back' => '支払い一覧に戻る',

        // Fallback
        'not_available' => '利用不可',
    ],

    'pages' => [
        // General
        'title' => 'ページ',
        'choose_file' => 'ファイルを選択',

        // Create Page
        'create' => 'ページを作成',
        'form_title' => 'タイトル (:code)',
        'form_content' => 'コンテンツ (:code)',
        'form_image' => '画像 (:code)',
        'form_save' => '保存',

        // Edit Page
        'edit' => 'ページを編集',
        'form_update' => '更新',

        // Pages Table
        'table_title' => 'タイトル',
        'table_slug' => 'スラッグ',
        'table_status' => 'ステータス',
        'table_actions' => '操作',

        // Delete Modal
        'delete_modal_title' => '削除の確認',
        'delete_modal_text' => 'このページを削除してもよろしいですか？',
        'delete_modal_cancel' => 'キャンセル',
        'delete_modal_delete' => '削除',

        // Toastr messages
        'toastr_success' => '成功',
        'toastr_error' => 'ページの削除中にエラーが発生しました',
    ],

    'customers' => [
        'customer_list' => '顧客一覧',

        // Create form
        'create_title' => '顧客を作成',
        'create_description' => '基本情報を入力して新しい顧客を追加します。',
        'create_button' => '顧客を作成',
        'password' => 'パスワード',

        // Table columns
        'id' => 'ID',
        'name' => '名前',
        'email' => 'メール',
        'phone' => '電話番号',
        'address' => '住所',
        'status' => 'ステータス',
        'actions' => '操作',

        // Status labels
        'active' => '有効',
        'inactive' => '無効',

        // Modal
        'confirm_delete_title' => '削除の確認',
        'confirm_delete_message' => 'この顧客を削除してもよろしいですか？',
        'cancel_button' => 'キャンセル',
        'delete_button' => '削除',

        // Toastr messages
        'success_title' => '成功',
        'deleted_title' => '削除済み',
        'delete_success_message' => '顧客が正常に削除されました！',
        'delete_error_message' => '顧客の削除中にエラーが発生しました！',

        // Details view
        'view_button' => '表示',
        'back_to_list' => '顧客一覧に戻る',
        'details_title' => '顧客詳細',
        'not_available' => '利用不可',
        'orders_count' => '注文数',
        'total_spent' => '累計購入額',
        'wishlist_count' => 'ウィッシュリスト商品数',
        'reviews_count' => 'レビュー件数',
        'basic_information' => '基本情報',
        'registered_at' => '登録日時',
        'last_updated' => '最終更新',

        // Addresses
        'addresses' => '住所',
        'address_name' => '住所名',
        'city' => '市区町村',
        'postal_code' => '郵便番号',
        'country' => '国',
        'default_address' => '既定の住所',
        'default_address_hint' => 'ストアフロントと管理画面での主要な配送先として使用されます。',
        'default' => '既定',
        'set_default' => '既定に設定',
        'set_as_default' => '既定として設定',
        'add_address' => '住所を追加',
        'delete' => '削除',
        'delete_confirm' => 'この住所を削除してもよろしいですか？',
        'no_addresses' => 'この顧客の住所は登録されていません。',
        'address_created' => '住所を保存しました。',
        'address_updated' => '住所を更新しました。',
        'address_deleted' => '住所を削除しました。',
        'address_set_default' => '既定の住所を更新しました。',

        // Orders
        'orders' => '注文',
        'orders_table_id' => '注文',
        'orders_table_status' => 'ステータス',
        'orders_table_total' => '合計金額',
        'orders_table_items' => '商品点数',
        'orders_table_placed_at' => '注文日時',
        'orders_table_payment_status' => '最新の支払い',
        'orders_table_action' => '操作',
        'view_order' => '注文を表示',
        'no_orders' => 'この顧客の注文は見つかりませんでした。',

        // Wishlist
        'wishlist_title' => 'ウィッシュリスト',
        'wishlist_product' => '商品',
        'wishlist_added_at' => '追加日時',
        'no_wishlists' => 'この顧客のウィッシュリストにはアイテムがありません。',
        'product_missing' => '利用できない商品',

        // Reviews
        'reviews_title' => '商品レビュー',
        'reviews_product' => '商品',
        'reviews_rating' => '評価',
        'reviews_status' => 'ステータス',
        'reviews_submitted_at' => '投稿日時',
        'reviews_content' => 'レビュー内容',
        'review_status_approved' => '承認済み',
        'review_status_pending' => '承認待ち',
        'no_reviews' => 'この顧客が投稿したレビューはありません。',
    ],

    'vendors' => [
        'title_list' => 'ベンダーリスト',
        'id' => 'ID',
        'name' => '名前',
        'email' => 'メール',
        'phone' => '電話番号',
        'status' => 'ステータス',
        'actions' => '操作',
        'register_new_vendor' => '新しいベンダーを登録',
        'vendor_name' => 'ベンダー名',
        'vendor_email' => 'ベンダーのメールアドレス',
        'phone_optional' => '電話番号（任意）',
        'password' => 'パスワード',
        'confirm_password' => 'パスワード確認',
        'status' => 'ステータス',
        'active' => '有効',
        'inactive' => '無効',
        'banned' => '禁止',
        'register_button' => 'ベンダーを登録',
        'cancel_button' => 'キャンセル',

        'active' => 'アクティブ',
        'inactive' => '非アクティブ',

        'modal_confirm_delete_title' => '削除の確認',
        'modal_confirm_delete_body' => 'このベンダーを削除してもよろしいですか？',
        'delete' => '削除',
        'cancel' => 'キャンセル',

        'success' => '成功',
        'success_delete' => 'ベンダーが正常に削除されました！',
        'error_delete' => 'ベンダーの削除中にエラーが発生しました。もう一度お試しください。',
    ],

    'languages' => [
        'english' => '英語',
        'spanish' => 'スペイン語',
        'french' => 'フランス語',
        'arabic' => 'アラビア語',
        'german' => 'ドイツ語',
        'persian' => 'ペルシャ語（ファルシ）',
        'hindi' => 'ヒンディー語',
        'indonesian' => 'インドネシア語',
        'italian' => 'イタリア語',
        'japanese' => '日本語',
        'korean' => '韓国語',
        'dutch' => 'オランダ語',
        'polish' => 'ポーランド語',
        'portuguese' => 'ポルトガル語',
        'russian' => 'ロシア語',
        'thai' => 'タイ語',
        'turkish' => 'トルコ語',
        'vietnamese' => 'ベトナム語',
        'chinese' => '中国語',

        'language_change' => '言語変更',
        'change_language' => '言語を変更する',
        'confirm_language_change' => '本当に言語を変更しますか？',
        'cancel' => 'キャンセル',
        'yes_change' => 'はい、変更する',
    ],

    'categories' => [
        'heading' => 'カテゴリ',
        'image' => '画像',
        'choose_file' => 'ファイルを選択',
        'id' => '識別子',
        'name' => 'カテゴリ名',
        'description' => '説明',
        'action' => 'アクション',
        'edit' => '編集',
        'delete' => '削除',
        'button' => '保存',
        'add_new' => '新規追加',
        'status' => 'ステータス',
        'created' => 'カテゴリが正常に作成されました！',
        'updated' => 'カテゴリが正常に更新されました！',
        'deleted' => 'カテゴリが正常に削除されました！',
        'status_updated' => 'カテゴリのステータスが正常に更新されました！',
        'massage_confirm' => '削除確認',
        'confirm_delete' => '本当にこのカテゴリを削除してもよろしいですか？',
        'massage_cancel' => 'キャンセル',
        'massage_delete' => '削除',
        'success' => '成功',
    ],

    'dashboard' => [
        'title' => 'Performance overview',
        'overview_subtitle' => 'Real-time metrics that highlight how your marketplace is performing today.',
        'daily_revenue' => "Today's Revenue",
        'daily_revenue_description' => 'Completed payments collected today.',
        'change_positive' => ':value% increase vs previous period',
        'change_negative' => ':value% decrease vs previous period',
        'change_neutral' => 'No change vs previous period',
        'yesterday_revenue' => 'Yesterday',
        'weekly_revenue' => 'Last 7 Days Revenue',
        'weekly_revenue_description' => 'Completed revenue across the last 7 days.',
        'weekly_revenue_change' => 'Trend unavailable – not enough historical data yet.',
        'net_revenue' => 'Net Revenue',
        'net_revenue_description' => 'Revenue after subtracting completed refunds.',
        'refunds' => 'Refunds',
        'average_order_value' => 'Average Order Value',
        'average_order_value_description' => 'Average revenue per completed order.',
        'order_completion_rate' => 'Order Completion Rate',
        'order_completion_rate_description' => 'Share of orders that reached completion.',
        'completed_orders' => 'Completed',
        'open_orders' => 'Open Orders',
        'open_orders_description' => 'Orders currently pending attention.',
        'pending_orders' => 'Pending',
        'processing_orders' => 'Processing',
        'cancelled_orders' => 'Cancelled',
        'customers' => 'Customers',
        'customers_description' => 'Total registered customers.',
        'new_customers' => 'New this month',
        'customers_growth' => 'Growth data unavailable – add more history to compare.',
        'vendors_description' => 'Vendors participating in the marketplace.',
        'revenue_trend' => 'Revenue Trend (last 7 days)',
        'no_data' => 'No data available',
        'order_status_breakdown' => 'Order Status Breakdown',
        'open_orders_total' => 'Orders analysed',
        'top_products' => 'Top Products by Reviews',
        'reviews' => 'Reviews',
        'unit_price' => 'Unit Price',
        'insights' => 'Insights',
        'insight_completion' => 'Completion rate is :value% across :total orders.',
        'direction_up' => 'increased',
        'direction_down' => 'decreased',
        'direction_flat' => 'held steady',
        'insight_weekly_revenue' => 'Weekly revenue :direction by :value% versus the previous week.',
        'insight_refund_rate' => 'Refunds represent :value% of total revenue.',
        'insight_customers' => 'Customer base changed by :value% month-over-month.',
    ],

    'datatables' => [
        'sEmptyTable' => 'テーブルにデータはありません',
        'sInfo' => '_START_ から _END_ まで、合計 _TOTAL_ 件のエントリ',
        'sInfoEmpty' => '0 から 0 まで、合計 0 件のエントリ',
        'sInfoFiltered' => '(合計 _MAX_ 件のエントリからフィルタリング)',
        'sLengthMenu' => '_MENU_ 件を表示',
        'sLoadingRecords' => '読み込み中...',
        'sProcessing' => '処理中...',
        'sSearch' => '検索:',
        'sZeroRecords' => '一致するレコードはありません',
        'oPaginate' => [
            'sFirst' => '最初',
            'sLast' => '最後',
            'sNext' => '次',
            'sPrevious' => '前',
        ],
    ],

    'products' => [

        // タイトル
        'title_create' => '商品を作成',
        'title_edit' => '商品を編集',
        'title_manage' => '商品を管理',

        // フォームフィールド
        'vendor' => 'ベンダー',
        'select_vendor' => 'ベンダーを選択',
        'product_name' => '商品名',
        'product_type' => '商品タイプ',
        'description' => '説明',
        'translations' => '翻訳',
        'translated_value' => '翻訳済みの値',
        'category' => 'カテゴリー',
        'brand' => 'ブランド',
        'no_brand' => 'ブランドなし',
        'status' => 'ステータス',
        'price' => '価格',
        'discount_price' => '割引価格',
        'sku' => 'SKU（在庫管理単位）',
        'barcode' => 'バーコード',
        'stock' => '在庫',
        'weight' => '重量',
        'dimension' => '寸法',
        'size' => 'サイズ',
        'color' => '色',
        'images' => '商品画像',
        'is_primary' => 'メインバリエーション',
        'variant_name_en' => 'バリエーション名（英語）',
        'attributes' => '属性',
        'attribute_values' => '属性値',
        'variants' => 'バリエーション',

        // ボタン
        'add_variant' => 'バリエーションを追加',
        'remove_variant' => 'バリエーションを削除',
        'save_product' => '商品を保存',
        'update_product' => '商品を更新',
        'choose_images' => '画像を選択',
        'choose_file' => 'ファイルを選択',
        'remove' => '削除',

        // メッセージ
        'status_updated' => '商品のステータスが正常に更新されました！',
        'success_create' => '商品が正常に作成されました！',
        'success_update' => '商品が正常に更新されました！',
        'success_delete' => '商品が正常に削除されました！',
        'delete_confirmation' => 'この商品を削除してもよろしいですか？',
        'success' => '成功',

        // テーブル列
        'id' => 'ID',
        'name' => '名前',
        'type' => 'タイプ',
        'price_column' => '価格',
        'status_column' => 'ステータス',
        'action' => '操作',

        // 確認ダイアログ
        'confirm_delete' => '削除の確認',
        'delete' => '削除',
        'cancel' => 'キャンセル',
    ],

    'brands' => [
        'heading' => 'ブランド',
        'id' => '識別子',
        'name' => 'ブランド名',
        'description' => '説明',
        'logo' => 'ロゴ',
        'status' => '状態',
        'action' => 'アクション',
        'edit' => '編集',
        'delete' => '削除',
        'create' => '作成',
        'update' => '更新',
        'add_new' => '新規追加',
        'button' => '保存',
        'form_title' => 'ブランドを作成または編集',
        'file_upload' => 'ロゴをアップロード',
        'no_logo' => 'ロゴなし',
        'choose_file' => 'ファイルを選択',
        'image_preview' => '画像プレビュー',
        'delete_confirmation' => '本当にこのブランドを削除してもよろしいですか？',
        'brand_deleted' => 'ブランドが正常に削除されました！',
        'error_delete' => 'ブランドの削除中にエラーが発生しました！',
        'created' => 'ブランドが正常に作成されました！',
        'updated' => 'ブランドが正常に更新されました！',
        'deleted' => 'ブランドが正常に削除されました！',
        'status_updated' => 'ブランドのステータスが正常に更新されました！',
        'massage_confirm' => '削除確認',
        'confirm_delete' => 'このブランドを削除してもよろしいですか？',
        'massage_cancel' => 'キャンセル',
        'massage_delete' => '削除',
        'success' => '成功',
    ],

    'banners' => [
        'button_back' => '戻る',
        'description' => '説明',
        'choose_file' => 'ファイルを選択',
        'all_banners' => 'すべてのバナー',
        'id' => '識別子',
        'add_new' => '新規追加',
        'banner_type' => 'バナータイプ',
        'promotion' => 'プロモーション',
        'sale' => 'セール',
        'seasonal' => '季節限定',
        'featured' => '特集',
        'announcement' => 'お知らせ',
        'actions' => 'アクション',
        'edit' => '編集',
        'delete' => '削除',
        'no_image' => '画像はありません',
        'delete_confirmation' => 'このバナーを削除してもよろしいですか？',
        'banner_deleted' => 'バナーが正常に削除されました！',
        'error_delete' => 'バナーの削除中にエラーが発生しました！',
        'image_preview' => '画像プレビュー',
        'create_banner' => 'バナー作成',
        'edit_banner' => 'バナーの翻訳を編集',
        'save' => '保存',
        'languages' => '言語',
        'status' => 'ステータス',
        'image' => '画像',
        'image_title' => '画像タイトル',
        'title' => 'タイトル',
        'select_language' => '言語を選択',
        'file_upload' => '画像をアップロード',
        'choose_file' => 'ファイルを選択',
        'existing_image' => '既存の画像',
        'banner_saved' => 'バナーが正常に保存されました！',
        'banner_updated' => 'バナーが正常に更新されました！',
        'form_title' => 'バナーを作成または編集',
        'form_description' => 'バナーに必要な情報をすべて提供してください。',
        'back_to_list' => 'バナーリストに戻る',
        'created' => 'バナーが正常に作成されました！',
        'updated' => 'バナーが正常に更新されました！',
        'deleted' => 'バナーが正常に削除されました！',
        'status_updated' => 'バナーのステータスが正常に更新されました！',
        'massage_confirm' => '削除確認',
        'confirm_delete' => 'このバナーを削除してもよろしいですか？',
        'massage_cancel' => 'キャンセル',
        'massage_delete' => '削除',
        'success' => '成功',
    ],

    'menus' => [
        'all_menus' => 'すべてのメニュー',
        'id' => '識別子',
        'add_new' => '新規追加',
        'button_create' => '作成',
        'button_update' => '更新',
        'title' => 'タイトル',
        'edit' => '編集',
        'action' => 'アクション',
        'delete' => '削除',
        'created_at' => '作成日',
        'no_menus' => 'メニューはありません',
        'delete_confirmation' => '本当にこのメニューを削除してもよろしいですか？',
        'menu_deleted' => 'メニューは正常に削除されました！',
        'error_delete' => 'メニューの削除中にエラーが発生しました！',
        'create_menu' => 'メニュー作成',
        'edit_menu' => 'メニュー編集',
        'save' => '保存',
        'menu_title' => 'メニュータイトル',
        'form_title' => 'メニューの作成または編集',
        'form_description' => 'メニューに必要な情報をすべて提供してください。',
        'back_to_list' => 'メニュー一覧に戻る',
        'created' => 'メニューが正常に作成されました！',
        'updated' => 'メニューが正常に更新されました！',
        'deleted' => 'メニューが正常に削除されました！',
        'status_updated' => 'メニューのステータスが正常に更新されました！',
        'massage_confirm' => '削除確認',
        'confirm_delete' => 'このメニューを削除してもよろしいですか？',
        'massage_cancel' => 'キャンセル',
        'massage_delete' => '削除',
        'success' => '成功',

    ],

    'menu_items' => [
        'heading' => 'すべてのメニューアイテム',
        'id' => '識別子',
        'create' => 'メニューアイテム作成',
        'choose_an_option' => 'オプションを選択',
        'select_an_option' => 'オプションを選択してください',
        'option1' => 'オプション 1',
        'option2' => 'オプション 2',
        'option3' => 'オプション 3',
        'option4' => 'オプション 4',
        'order_number' => '順番',
        'parent_item' => '親アイテム',
        'parent_none' => 'なし',
        'edit' => 'メニューアイテム編集',
        'update' => 'メニューアイテム更新',
        'delete' => 'メニューアイテム削除',
        'title' => 'タイトル',
        'button' => '保存',
        'update_button' => '更新',
        'slug' => 'スラッグ',
        'order' => '順序',
        'actions' => 'アクション',
        'add_new' => '新規追加',
        'submit' => '送信',
        'cancel' => 'キャンセル',
        'no_title' => 'タイトルなし',
        'select_menu' => 'メニューを選択',
        'select_parent_item' => '親アイテムを選択',
        'language' => '言語',
        'select_language' => '言語を選択',
        'select_order' => '順番を選択',
        'success_message' => 'メニューアイテムが正常に作成されました！',
        'error_message' => 'メニューアイテムの作成中にエラーが発生しました。',
        'confirm_delete' => '本当にこのメニューアイテムを削除してもよろしいですか？',
        'update_success_message' => 'メニューアイテムが正常に更新されました！',
        'update_error_message' => 'メニューアイテムの更新中にエラーが発生しました。',
        'created' => 'メニューアイテムが正常に作成されました！',
        'updated' => 'メニューアイテムが正常に更新されました！',
        'deleted' => 'メニューアイテムが正常に削除されました！',
        'status_updated' => 'メニューアイテムのステータスが正常に更新されました！',
        'massage_confirm' => '削除の確認',
        'confirm_delete' => 'このメニューアイテムを削除してもよろしいですか？',
        'massage_cancel' => 'キャンセル',
        'massage_delete' => '削除',
        'success' => '成功',

    ],
    'errors' => [
        'validation_failed' => '検証に失敗しました！エラーを修正して再試行してください。',
        'csrf_token_invalid' => '無効なCSRFトークンです。ページを更新して再試行してください。',
        'not_found' => '要求されたアイテムが見つかりません。',
        'unauthorized' => 'このアクションを実行する権限がありません。',
    ],
    'messages' => [
        'welcome' => '管理パネルへようこそ！',
        'dashboard' => 'ダッシュボード',
        'settings' => '設定',
        'log_out' => 'ログアウト',
        'profile' => 'プロフィール',
        'menu' => 'メニュー',
        'home' => 'ホーム',
        'view_details' => '詳細を見る',
    ],

    'social_media_links' => [
        'type' => 'ソーシャルネットワークの種類',
        'select_type' => 'ソーシャルネットワークの種類を選択',
        'types' => [
            'facebook' => 'フェイスブック',
            'instagram' => 'インスタグラム',
            'tiktok' => 'ティックトック',
            'youtube' => 'ユーチューブ',
            'x' => 'エックス',
        ],

        'create' => 'ソーシャルメディアリンクの作成',
        'edit' => 'ソーシャルメディアリンクの編集',
        'platform' => 'プラットフォーム名',
        'link' => 'ソーシャルメディアリンク',
        'created' => 'ソーシャルメディアリンクが正常に作成されました!',
        'updated' => 'ソーシャルメディアリンクが正常に更新されました!',
        'deleted' => 'ソーシャルメディアリンクが正常に削除されました!',
        'status_updated' => 'ソーシャルメディアリンクのステータスが正常に更新されました!',
        'massage_confirm' => '削除を確認',
        'confirm_delete' => 'このソーシャルメディアリンクを削除してもよろしいですか?',
        'massage_cancel' => 'キャンセル',
        'massage_delete' => '削除',
        'success' => '成功',

        'translations' => [
            'platform_name' => 'プラットフォーム名（翻訳済み）',
        ],
        'save' => '保存',
        'update' => '更新',
        'delete' => '削除',
        'no_links' => '利用可能なソーシャルメディアリンクはありません',
        'delete_confirmation' => 'このリンクを削除してもよろしいですか？',
        'link_deleted' => 'ソーシャルメディアリンクが正常に削除されました！',
        'error_delete' => 'リンク削除中にエラーが発生しました！',
        'create_link' => 'ソーシャルメディアリンクの作成',
        'edit_link' => 'ソーシャルメディアリンクの編集',
        'form_title' => 'ソーシャルメディアリンクの作成または編集',
        'form_description' => 'ソーシャルメディアリンクに必要な情報をすべて提供してください。',
        'back_to_list' => 'ソーシャルメディアリンク一覧に戻る',
        'add_new' => '新規追加',
        'trans_name' => '翻訳された名前',
        'delete' => '削除',
        'action' => 'アクション',
    ],

    'orders' => [

        // Page Title
        'title' => '注文一覧',

        // Table Headings
        'id' => '注文ID',
        'order_date' => '注文日',
        'status' => 'ステータス',
        'total_price' => '合計金額',
        'action' => '操作',

        // Delete Modal
        'delete_confirm_title' => '削除の確認',
        'delete_confirm_message' => 'この注文を削除してもよろしいですか？',
        'delete_cancel' => 'キャンセル',
        'delete_button' => '削除',

        // Toastr / Flash Messages
        'deleted_success' => '注文は正常に削除されました。',
        'deleted_error' => '注文の削除に失敗しました。',
        'deleted' => '削除済み',
    ],

    'attributes' => [
        'title_create' => '属性を作成',
        'title_edit' => '属性を編集',
        'title_manage' => '属性を管理',

        'attribute_name' => '属性名',
        'attribute_values' => '属性の値',
        'translations' => '翻訳',
        'translated_value' => '翻訳された値',

        'add_value' => '値を追加',
        'remove_value' => '削除',
        'save_attribute' => '属性を保存',
        'update_attribute' => '属性を更新',
        'add_value_translation' => '値の翻訳を追加',

        'success_create' => '属性が正常に作成されました！',
        'success_update' => '属性が正常に更新されました！',
        'success_delete' => '属性が正常に削除されました！',
        'delete_confirmation' => 'この属性を本当に削除しますか？',
        'success' => '成功',

        'id' => 'ID',
        'name' => '名前',
        'values' => '値',
        'action' => 'アクション',

        'confirm_delete' => '削除の確認',
        'delete' => '削除',
        'cancel' => 'キャンセル',
    ],

    'product_reviews' => [
        'title_manage' => '商品レビュー管理',
        'index_description' => '顧客からのフィードバックを管理し、カタログの信頼性を保ちます。',
        'show_title' => 'レビュー #:id',
        'details_title' => 'レビュー詳細',
        'back_to_list' => 'レビュー一覧へ戻る',

        'review_id' => 'レビューID',
        'customer_name' => '顧客名',
        'product_name' => '商品名',
        'rating' => '評価',
        'status' => 'ステータス',
        'actions' => '操作',
        'review' => 'レビュー内容',
        'view' => '閲覧',
        'edit' => '編集',

        'active' => '有効',
        'inactive' => '無効',
        'approved' => '承認済み',
        'pending' => '保留',
        'missing_customer' => '不明な顧客',
        'missing_product' => '不明な商品',
        'no_review_provided' => 'このレビューには内容が入力されていません。',

        'status_filter_label' => 'ステータスで絞り込む',
        'status_filter_all' => 'すべてのステータス',

        'submitted_at' => '送信日時',
        'updated_at' => '最終更新',

        'edit_title' => 'レビュー #:id を編集',
        'edit_form_title' => 'レビューを更新',
        'edit_description' => 'このレビューの評価や承認ステータスを調整します。',
        'update_button' => '変更を保存',

        'confirm_delete' => '削除の確認',
        'delete_message' => 'この商品レビューを削除してもよろしいですか？',
        'delete' => '削除',
        'cancel' => 'キャンセル',

        'success_create' => '商品レビューを作成しました。',
        'success_update' => '商品レビューを更新しました。',
        'success_delete' => '商品レビューを削除しました。',
        'error_delete' => '商品レビューの削除中にエラーが発生しました。もう一度お試しください。',

        'success' => '成功',
        'error' => 'エラー',
    ],

];
