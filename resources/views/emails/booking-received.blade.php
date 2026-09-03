<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">

<div style="background: #283e46; padding: 30px; text-align: center;">
    <h1 style="color: white; margin: 0; font-weight: 300;">УЮТНЫЙДОМ</h1>
</div>

<div style="padding: 30px; background: #ffffff;">
    <h2 style="color: #283e46; margin-top: 0;">Здравствуйте, {{ $booking->guest_name }}!</h2>

    <p>
        Спасибо за заявку на бронирование! Мы получили её и свяжемся с вами
        в течение в ближайшее время через {{ $booking->contact_method === 'phone' ? 'телефон' : $booking->contact_method }}.
    </p>

    <div style="background: #ebf7fb; border-radius: 12px; padding: 20px; margin: 20px 0;">
        <h3 style="margin-top: 0; color: #283e46;">Детали заявки:</h3>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 5px 0; color: #666;">Объект:</td>
                <td style="padding: 5px 0; text-align: right;"><strong>{{ $property->title }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #666;">Заезд:</td>
                <td style="padding: 5px 0; text-align: right;"><strong>{{ $booking->check_in->format('d.m.Y') }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #666;">Выезд:</td>
                <td style="padding: 5px 0; text-align: right;"><strong>{{ $booking->check_out->format('d.m.Y') }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #666;">Гостей:</td>
                <td style="padding: 5px 0; text-align: right;"><strong>{{ $booking->adults }} взр., {{ $booking->children }} дет.</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #666;">Сумма:</td>
                <td style="padding: 5px 0; text-align: right;"><strong style="color: #77c4db; font-size: 18px;">{{ number_format($booking->total_price, 0, '.', ' ') }} ₽</strong></td>
            </tr>
        </table>
    </div>

    @if($hasContract)
        <p>
            <strong>Договор аренды прикреплён к этому письму.</strong><br>
            Пожалуйста, ознакомьтесь с ним перед заселением.
        </p>
    @else
        <p>
            Договор аренды будет направлен вам после подтверждения бронирования.
        </p>
    @endif

    <div style="background: #fff8e1; border-left: 4px solid #fbbf24; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <strong>Важно:</strong> бронирование считается подтверждённым только после
        одобрения заявки нашим администратором.
    </div>

    <p style="margin-top: 30px;">
        С уважением,<br>
        <strong>Команда УЮТНЫЙДОМ</strong><br>
        +7 (999) 999-99-99<br>
        info@uyutnydom.ru
    </p>
</div>

<div style="background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #999;">
    Это автоматическое письмо. Пожалуйста, не отвечайте на него.<br>
    &copy; {{ date('Y') }} УЮТНЫЙДОМ
</div>

</body>
</html>
