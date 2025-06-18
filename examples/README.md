# Garanti Bankası Sanal POS Entegrasyonu Örnekleri

Bu dizin, Garanti Bankası Sanal POS entegrasyonu için örnek kodlar içerir. Bu örnekler, paketi nasıl kullanacağınızı göstermek için tasarlanmıştır.

## Örnekler

### 1. 3D Secure Ödeme

`3d_secure_payment.php` dosyası, 3D Secure ödeme başlatma işlemini gösterir. Bu örnek:

- Yapılandırma oluşturma
- Ödeme isteği oluşturma
- 3D Secure ödeme başlatma
- Kullanıcıyı 3D Secure sayfasına yönlendirme

### 2. 3D Secure Callback İşleme

`3d_secure_callback.php` dosyası, 3D Secure ödeme sonrası callback işlemini gösterir. Bu örnek:

- Banka tarafından gönderilen callback verilerini işleme
- 3D Secure ödeme işlemini tamamlama
- Başarılı ve başarısız ödeme durumlarını işleme

### 3. Normal Ödeme (3D Secure olmadan)

`regular_payment.php` dosyası, 3D Secure olmadan normal ödeme işlemini gösterir. Bu örnek:

- Ödeme isteği oluşturma
- Normal ödeme işlemi yapma
- Başarılı ve başarısız ödeme durumlarını işleme

## Kullanım

1. Örnek dosyaları web sunucunuza yükleyin
2. Dosyalardaki yapılandırma bilgilerini kendi Garanti Bankası hesap bilgilerinizle güncelleyin:
   - `merchantId`
   - `terminalId`
   - `userId`
   - `password`
3. 3D Secure ödeme için callback URL'sini kendi callback URL'niz ile değiştirin

## Notlar

- Bu örnekler eğitim amaçlıdır ve gerçek ortamda kullanmadan önce güvenlik önlemlerini almanız gerekir
- Gerçek kart bilgilerini test amaçlı olarak kullanmayın, test kartları kullanın
- Canlı ortama geçmeden önce test ortamında kapsamlı testler yapın