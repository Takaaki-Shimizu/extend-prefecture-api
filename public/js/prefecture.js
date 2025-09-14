document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('prefecture-form');
    const addressInput = document.getElementById('address');
    const resultDiv = document.getElementById('result');
    const submitButton = form.querySelector('button[type="submit"]');

    // API endpoint from config
    const API_ENDPOINT = '/api/extract-prefecture';

    function fillAddress(address) {
        addressInput.value = address;
        addressInput.focus();
    }

    function showResult(message, isSuccess) {
        resultDiv.style.display = 'block';
        resultDiv.className = isSuccess ? 'success' : 'error';

        // Clear previous content
        resultDiv.innerHTML = '';

        // Create title element
        const titleDiv = document.createElement('div');
        titleDiv.className = 'result-title';
        titleDiv.textContent = isSuccess ? '抽出結果' : 'エラー';
        resultDiv.appendChild(titleDiv);

        // Create message element - safely handle HTML content
        const messageDiv = document.createElement('div');
        if (isSuccess && typeof message === 'string' && message.includes('抽出された都道府県:')) {
            const parts = message.split(': ');
            if (parts.length === 2) {
                const labelSpan = document.createElement('span');
                labelSpan.textContent = parts[0] + ': ';
                const prefectureSpan = document.createElement('strong');
                prefectureSpan.textContent = parts[1].replace(/<\/?strong>/g, '');
                messageDiv.appendChild(labelSpan);
                messageDiv.appendChild(prefectureSpan);
            } else {
                messageDiv.textContent = message.replace(/<\/?strong>/g, '');
            }
        } else {
            messageDiv.textContent = message;
        }
        resultDiv.appendChild(messageDiv);
    }

    function showLoading() {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="loading"></span>処理中...';
    }

    function hideLoading() {
        submitButton.disabled = false;
        submitButton.innerHTML = '都道府県を抽出';
    }

    function validateAddress(address) {
        if (!address || address.trim().length === 0) {
            return '住所を入力してください。';
        }
        if (address.length > 200) {
            return '住所は200文字以内で入力してください。';
        }
        return null;
    }

    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const address = addressInput.value.trim();
        const validationError = validateAddress(address);

        if (validationError) {
            showResult(validationError, false);
            return;
        }

        showLoading();
        resultDiv.style.display = 'none';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                throw new Error('CSRFトークンが見つかりません。');
            }

            const response = await fetch(API_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                },
                body: JSON.stringify({ address: address })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.prefecture) {
                showResult(`抽出された都道府県: ${data.prefecture}`, true);
            } else {
                showResult(data.message || 'データの形式が正しくありません。', false);
            }
        } catch (error) {
            console.error('Error:', error);
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                showResult('ネットワークエラーが発生しました。インターネット接続を確認してください。', false);
            } else if (error.name === 'SyntaxError') {
                showResult('サーバーからの応答が正しくありません。', false);
            } else {
                showResult('通信エラーが発生しました。しばらくしてからもう一度お試しください。', false);
            }
        } finally {
            hideLoading();
        }
    });

    // Handle example button clicks
    document.querySelectorAll('.example-btn').forEach(button => {
        button.addEventListener('click', function() {
            const address = this.dataset.address;
            fillAddress(address);
        });
    });

    // Make fillAddress function globally accessible for backwards compatibility
    window.fillAddress = fillAddress;
});