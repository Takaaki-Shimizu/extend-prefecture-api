<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>都道府県抽出API</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/prefecture.css') }}?v={{ config('app.version', '1.0') }}">
</head>
<body>
    <div class="container">
        <h1>都道府県抽出API</h1>

        <form id="prefecture-form">
            <div class="form-group">
                <label for="address">住所を入力してください</label>
                <input type="text" id="address" name="address" maxlength="200" required
                       placeholder="例: 〒314-0007 茨城県鹿嶋市神向寺後山２６−２">
            </div>
            <button type="submit">都道府県を抽出</button>
        </form>

        <div id="result"></div>

        <div class="examples">
            <h3>入力例</h3>
            <ul>
                <li><button type="button" class="example-btn" data-address="〒314-0007 茨城県鹿嶋市神向寺後山２６−２">〒314-0007 茨城県鹿嶋市神向寺後山２６−２</button></li>
                <li><button type="button" class="example-btn" data-address="茨城県鹿嶋市神向寺後山２６−２">茨城県鹿嶋市神向寺後山２６−２</button></li>
                <li><button type="button" class="example-btn" data-address="鹿嶋市神向寺後山２６−２">鹿嶋市神向寺後山２６−２</button></li>
                <li><button type="button" class="example-btn" data-address="京都市中京区烏丸通二条下ル二条殿町538">京都市中京区烏丸通二条下ル二条殿町538</button></li>
                <li><button type="button" class="example-btn" data-address="東京都新宿区歌舞伎町1-1-1">東京都新宿区歌舞伎町1-1-1</button></li>
            </ul>
        </div>
    </div>

    <script src="{{ asset('js/prefecture.js') }}?v={{ config('app.version', '1.0') }}"></script>
</body>
</html>