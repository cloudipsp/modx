Модуль Fondy для ModX revo minishop2.
=====

#Инструкция по установке модуля оплаты Fondy

1. Скопируйте каталог assets и core в корень системы modx.

2. В админ.панели Modx зайдите: Приложения -> Minishop2 -> Настройки -> Способы оплаты -> Создать 

2.1 Заполните все поля, в поле Класс-обработчик указать fondy.

Скриншот настроек:
[1]: https://raw.githubusercontent.com/cloudipsp/modx/master/settings.jpg

3. Открыть файл для редактирования core/components/minishop2/custom/payment/fondy.class.php

в верху заполнить поля:

MERCHANT_ID - можно узнать в личном кабинете Fondy
SECRET_KEY - можно узнать в личном кабинете Fondy
SUCCESS_URL - ссылка для перехода на страницу после успешного платежа

Скриншот настроек:
[1]: https://raw.githubusercontent.com/cloudipsp/modx/master/settings1.png

Сохраните и обновите файл на сервере.

