<?php

namespace App\Action\Api;

class ChatbotSuggestionsAction
{
    public function execute(string $locale = 'en'): array
    {
        $isAr = $locale === 'ar';

        return $isAr
            ? [
                ['id' => 'faq', 'label' => 'أسئلة شائعة', 'question' => 'ما هي الأسئلة الشائعة؟'],
                ['id' => 'orders', 'label' => 'تتبع الطلب', 'question' => 'كيف أتتبع طلبي؟'],
                ['id' => 'payment', 'label' => 'طرق الدفع', 'question' => 'ما طرق الدفع المتاحة؟'],
                ['id' => 'products', 'label' => 'المنتجات والمفضلة', 'question' => 'ما المنتجات المتاحة والعروض؟'],
                ['id' => 'offers', 'label' => 'كوبونات وعروض', 'question' => 'ما العروض وكوبونات الخصم الحالية؟'],
            ]
            : [
                ['id' => 'faq', 'label' => 'FAQs', 'question' => 'What are the frequently asked questions?'],
                ['id' => 'orders', 'label' => 'Track order', 'question' => 'How do I track my order?'],
                ['id' => 'payment', 'label' => 'Payment methods', 'question' => 'What payment methods do you accept?'],
                ['id' => 'products', 'label' => 'Products & favorites', 'question' => 'What products and offers do you have?'],
                ['id' => 'offers', 'label' => 'Coupons & offers', 'question' => 'What promo codes or offers are available?'],
            ];
    }
}