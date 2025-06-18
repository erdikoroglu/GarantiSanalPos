# Garanti Bankası Sanal POS Entegrasyonu

Bu paket, Türkiye Cumhuriyeti Garanti Bankası Sanal POS sistemine entegrasyon sağlamak için geliştirilmiştir. 3D Secure ve normal ödeme işlemlerini destekler.

## Kurulum

Paketi Composer ile kurabilirsiniz:

```bash
composer require w3/garanti-sanalpos
```

## Kullanım

### Yapılandırma

```php
use W3\GarantiSanalPos\Config;
use W3\GarantiSanalPos\GarantiPosClient;

// Yapılandırma oluşturma
$config = new Config([
    'merchantId' => 'MERCHANT_ID',
    'terminalId' => 'TERMINAL_ID',
    'userId' => 'USER_ID',
    'password' => 'PASSWORD',
    'mode' => 'TEST', // veya 'PROD' canlı ortam için
]);

// Client oluşturma
$client = new GarantiPosClient($config);
```

### 3D Secure Ödeme

```php
use W3\GarantiSanalPos\Model\PaymentRequest;
use W3\GarantiSanalPos\Enum\Currency;

// Ödeme isteği oluşturma
$paymentRequest = new PaymentRequest();
$paymentRequest->setOrderId('ORDER_123456')
    ->setAmount(100.50) // TL cinsinden
    ->setCurrency(Currency::TRY)
    ->setCardNumber('4242424242424242')
    ->setCardExpireMonth('12')
    ->setCardExpireYear('2025')
    ->setCardCvv('123')
    ->setCardHolderName('John Doe')
    ->setInstallment(0); // Tek çekim

// 3D Secure başlatma
$response = $client->initiate3DPayment($paymentRequest, 'https://example.com/callback');

// 3D Secure sayfasına yönlendirme
echo $response->getHtmlContent();
```

### 3D Secure Callback İşleme

```php
// 3D Secure işlemi sonrası callback
$response = $client->complete3DPayment($_POST);

if ($response->isSuccess()) {
    echo "Ödeme başarılı. İşlem No: " . $response->getTransactionId();
} else {
    echo "Ödeme başarısız. Hata: " . $response->getErrorMessage();
}
```

### Normal Ödeme (3D Secure olmadan)

```php
use W3\GarantiSanalPos\Model\PaymentRequest;
use W3\GarantiSanalPos\Enum\Currency;

// Ödeme isteği oluşturma
$paymentRequest = new PaymentRequest();
$paymentRequest->setOrderId('ORDER_123456')
    ->setAmount(100.50) // TL cinsinden
    ->setCurrency(Currency::TRY)
    ->setCardNumber('4242424242424242')
    ->setCardExpireMonth('12')
    ->setCardExpireYear('2025')
    ->setCardCvv('123')
    ->setCardHolderName('John Doe')
    ->setInstallment(0); // Tek çekim

// Ödeme işlemi
$response = $client->makePayment($paymentRequest);

if ($response->isSuccess()) {
    echo "Ödeme başarılı. İşlem No: " . $response->getTransactionId();
} else {
    echo "Ödeme başarısız. Hata: " . $response->getErrorMessage();
}
```

## Lisans

Bu paket MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakınız.