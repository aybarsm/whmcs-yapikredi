<table><tr><td><h3>Bu modül Alastyr Telekomünikasyon A.Ş. (<a href="https://alastyr.com" target="_blank">https://alastyr.com</a>) için özel geliştirilmiş, açık kaynak yazılımı desteklemek amacıyla MIT lisansı ile yayınlanmıştır.</h3></td></tr></table>

## Ne Yapar
Bu WHMCS ödeme geçidi modülü, Yapı Kredi Bankası Posnet Sanal Pos sistemi ile entegre çalışmak üzere tasarlanmıştır. Modül, işte neler sağladığına dair genel bir bakış:

### 3D Güvenli Kimlik Doğrulama
- **3D Güvenli Altyapısı:** Modül, müşterilerinizin güvenli bir şekilde online ödemeler yapmasını sağlayan 3D Güvenli kimlik doğrulama altyapısına sahiptir.
- **Dinamik Yönlendirme:** 3D Güvenli kimlik doğrulama sürecini tamamladıktan sonra kullanıcıları dinamik olarak yönlendirir.

### 3D Güvenli Callback Yapısı
- **Güvenli Callback Yapısı:** 3D Güvenli işlem sonrası geri arama (callback) işlemlerini güvenli bir şekilde yönetir ve işler.
- **Dinamik Geri Bildirim:** İşlemin başarılı olup olmadığına dair dinamik geri bildirim sağlar.

### Tam ve Kısmi İade Yapısı
- **Esnek İade Seçenekleri:** Modül, tam veya kısmi iade işlemleri için esnek çözümler sunar.
- **Hızlı İade Süreci:** İade sürecini mümkün olan en kısa sürede tamamlamanıza yardımcı olur.
- **Kısmi İadeler:** Farklı ödeme türlerine göre kısmi iadeleri yönetme ve işleme kapasitesi.

### Farklı Ödeme Türleri için Yapı Sağlar
- Modül, yetkilendirme veya satış olmak üzere iki farklı ödeme türü için yapı sağlar. Bu, işlemlerinizin nasıl ele alınacağını seçmenize olanak tanır; anında mı yoksa onay alındıktan sonra mı.

### Dinamik Döviz Cinsinden Ödemeler için Yapı Sağlar
- Farklı para birimlerinde ödemeleri dinamik olarak ele alacak bir yapı sunar. Bu, çeşitli para birimlerinde işlem yapmanıza olanak tanır, böylece global müşterilere hizmet edebilirsiniz.

### Statik ve Oransal Ücretler için İşlem Ücreti Hesaplaması Yapısı
- Hem statik hem de oransal işlem ücretleri için işlem ücreti hesaplama yapısı sağlar. Bu, her işlem için doğru maliyeti hızlı ve kolay bir şekilde hesaplamak için kullanılabilir.

### Ödeme Serbest Bırakma Tarihi Hesaplama Yapısı
- Ödemelerin ne zaman serbest bırakılacağını hesaplamak için bir yapı sağlar. Bu, nakit akışınızı daha iyi yönetmenize ve planlamanıza yardımcı olur.

### Banka Test Ortamı için Yapı
- Geliştirme ve test aşamalarınızda kullanabileceğiniz bir banka test ortamı yapısı sağlar. Bu, sisteminizin doğru bir şekilde çalışıp çalışmadığını kontrol etmenize yardımcı olur.

### Kolay Entegrasyon
- **Hızlı Kurulum:** Modül, hızlı ve kolay kurulum özellikleriyle zaman kazandırır.
- **Gelişmiş Entegrasyon Seçenekleri:** Farklı iş modellerine ve ihtiyaçlara uygun çeşitli entegrasyon seçenekleri sunar.

## Özellikler

### Yüksek Özelleştirilebilir & Konfigüre Edilebilir
- **Dinamik Koşullar:** Modül, kullanıcıların banka sistem talep yapısını veya diğer özelleştirmeleri, çekirdek koduna müdahale etmeden kolayca değiştirmelerine olanak tanır.
- **Esnek Özelleştirme:** Oldukça uyumlu tasarımı ile kullanıcılar, modülün yeteneklerini benzersiz iş ihtiyaçlarına ve senaryolara uyacak şekilde kolayca ayarlayıp genişletebilir.

### Önbelleğe Alınmış Konfigürasyon Yapısı
- **Performans Optimizasyonu:** Önbelleğe alınmış konfigürasyon yapısı kullanılarak modül, gereksiz operasyonları azaltarak ve işlem sürecini akıcı hale getirerek optimal performans sağlar.
- **Hızlı Yükleme:** Önbelleğe alınmış konfigürasyonlar, hem yöneticiler hem de son kullanıcılar için daha hızlı yükleme süreleri ve daha akıcı deneyimler sağlar.

### Konfigürasyonda Kapanış (Closure) Desteği
- **İleri Düzey Özelleştirme:** Konfigürasyon dosyasındaki kapanış desteği, modülün çekirdek kodunu doğrudan değiştirmeye gerek kalmadan ileri düzey özelleştirme olasılıklarını mümkün kılar.
- **Akıcı Ayarlamalar:** Kapanış tabanlı özelleştirme sistemi sayesinde, işlevsellikteki değişiklikleri minimal çaba ile hızla implemente edin ve devreye alın.

### Konfigürasyon Tabanlı Tasarım
- **Kodsuz Ayarlamalar:** Modülün davranışı, özellikleri ve operasyonları, kod yazmadan veya değiştirmeden ayarlanabilir ve konfigüre edilebilir, tüm beceri seviyeleri için kullanıcı dostu bir yaklaşım sunar.
- **Tutarlılık Korunur:** Her şeyi konfigürasyon dosyalarından derleyerek, modül tüm işlem türleri için tutarlı ve güvenilir bir operasyon akışını korur.

### Geleceğe Yönelik Özelleştirme
- **Düşük Bakım:** Kapanış ve konfigürasyon tabanlı tasarımı sayesinde, modül gelecekteki WHMCS ve bankacılık sistemi güncellemeleri ile uyumlu kalacak şekilde bakım ve güncellemesi kolaydır.
- **Ölçeklenebilir Mimari:** Ölçeklenebilirlik gözetilerek inşa edilen bu modül, işletmeniz büyüdükçe artan işlem hacmini ve ek özelleştirme gereksinimlerini karşılayabilir.

## Başlamak İçin
**Ana WHMCS kurulum dizininizde, modüller dizininize depoyu klonlamak için aşağıdaki komutu çalıştırın.**
```bash
git clone https://github.com/aybarsm/whmcs-yapikredi.git modules/gateways/yapikredi
```
**Modül dizinine girin ve gerekli paketleri yüklemek için composer'i başlatın.**
```bash
cd modules/gateways/yapikredi
composer install
```
**WHMCS keşfi için gerekli dosyaları oluşturmak üzere aşağıdaki komutu çalıştırabilir veya modules/gateways dizinindeki her iki dosyayı da WHMCS kurulumunuzdaki aynı konumlara kopyalayabilirsiniz.**
```bash
php module install
```
**WHMCS Apps&Integrations ayarlarinizda Payments alanında modülü aktif hale getirebilirsiniz.**
[![WHMCS Yapı Kredi Apps and Integrations](https://i.postimg.cc/mgqXZMcB/whmcs-yapikredi-activation.png)](https://postimg.cc/LJtBNJBb)
[![WHMCS Yapı Kredi Activate](https://i.postimg.cc/N0q6Px99/whmcs-yapikredi-activate.png)](https://postimg.cc/sMmGM7Js)

**Son olarak modül ayarlarını gerçekleştirebilirsiniz.**
[![WHMCS Yapı Kredi Configuration](https://i.postimg.cc/VNf66S2Z/whmcs-yapikredi-config.png)](https://postimg.cc/DSNFpzv1)

## Uyumluluk
Bu modül minimum PHP 7.4 ve WHMCS 8.2.1 versiyonlarını gerektirmektedir. Tüm testler bu ortamda gerçekleştirilmiştir.