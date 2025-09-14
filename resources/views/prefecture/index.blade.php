<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>都道府県抽出API</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/prefecture.css') }}">
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
                <li onclick="fillAddress('〒314-0007 茨城県鹿嶋市神向寺後山２６−２')">〒314-0007 茨城県鹿嶋市神向寺後山２６−２</li>
                <li onclick="fillAddress('茨城県鹿嶋市神向寺後山２６−２')">茨城県鹿嶋市神向寺後山２６−２</li>
                <li onclick="fillAddress('鹿嶋市神向寺後山２６−２')">鹿嶋市神向寺後山２６−２</li>
                <li onclick="fillAddress('京都市中京区烏丸通二条下ル二条殿町538')">京都市中京区烏丸通二条下ル二条殿町538</li>
                <li onclick="fillAddress('東京都新宿区歌舞伎町1-1-1')">東京都新宿区歌舞伎町1-1-1</li>
            </ul>
        </div>
    </div>

    <script>
        const form = document.getElementById('prefecture-form');
        const addressInput = document.getElementById('address');
        const resultDiv = document.getElementById('result');
        const submitButton = form.querySelector('button[type="submit"]');

        function fillAddress(address) {
            addressInput.value = address;
        }

        function showResult(message, isSuccess) {
            resultDiv.style.display = 'block';
            resultDiv.className = isSuccess ? 'success' : 'error';
            resultDiv.innerHTML = `<div class="result-title">${isSuccess ? '抽出結果' : 'エラー'}</div>${message}`;
        }

        function showLoading() {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="loading"></span>処理中...';
        }

        function hideLoading() {
            submitButton.disabled = false;
            submitButton.innerHTML = '都道府県を抽出';
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const address = addressInput.value.trim();
            if (!address) {
                showResult('住所を入力してください。', false);
                return;
            }

            showLoading();
            resultDiv.style.display = 'none';

            try {
                const response = await fetch('/api/extract-prefecture', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ address: address })
                });

                const data = await response.json();

                if (response.ok) {
                    showResult(`抽出された都道府県: <strong>${data.prefecture}</strong>`, true);
                } else {
                    showResult(data.message || 'エラーが発生しました。', false);
                }
            } catch (error) {
                console.error('Error:', error);
                showResult('通信エラーが発生しました。しばらくしてからもう一度お試しください。', false);
            } finally {
                hideLoading();
            }
        });
    </script>
</body>
</html>