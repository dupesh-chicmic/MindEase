<?php

namespace Database\Seeders;

use App\Models\Suggestion;
use Illuminate\Database\Seeder;

class SuggestionSeeder extends Seeder
{
    public function run(): void
    {
        Suggestion::query()->delete();

        $now = now();
        $rows = [];

        foreach ($this->messagesByMood() as $mood => $messages) {
            foreach ($messages as $message) {
                $rows[] = [
                    'mood' => $mood,
                    'message' => $message,
                    'language' => 'hinglish',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            Suggestion::query()->insert($chunk);
        }
    }

    /**
     * @return array<string, list<string>>
     */
    private function messagesByMood(): array
    {
        return [
            'sad' => [
                'Bro tu strong hai 💪 ye phase bhi nikal jayega ❤️',
                'Rona okay hai, emotions ko space de — tu alone nahi hai 🤗',
                'Aaj bas breathe kar 🌬️ kal better feel hoga, promise ✨',
                'Tera heart heavy hai samajh raha hoon — thoda rest kar le 🫂',
                'Life kabhi kabhi zyada lagti hai, par tu handle karne layak hai 💯',
                'Chhoti si baat nahi hai jab dil dukhta hai — apne aap ko hug kar 🫶',
                'Slow ho jaa, koi race nahi hai — healing takes time ⏳💜',
                'Tu worthy hai khush rehne ke liye, yaad rakhna 🌈',
                'Aaj sirf survive karna bhi win hai — proud of you 🏆',
                'Negative thoughts ko mute kar de, playlist happy wali laga 🎧😌',
                'Ek cup chai + deep breath = mini reset ☕✨ try kar',
                'Jo hurt kar raha hai, usse distance okay hai — boundaries cool hain 🙌',
                'Tere emotions valid hain, kisi ko explain karne ki zaroorat nahi 💬',
                'Kal ka tu soch mat, aaj bas thoda gentle reh apne saath 🌸',
                'Sunshine wapas aayega — temporary clouds hain ☁️→☀️',
                'Tu bas itna yaad rakh: progress ≠ perfect, chal raha hai to enough hai 🚶‍♂️💚',
                'Dost ko text kar le, venting is self-care too 📱🤝',
                'Aaj Netflix + blanket = valid coping strategy 🛋️🍿 no guilt',
                'Tera smile wapas aayega — thoda time de khud ko ⏰❤️‍🩹',
                'Main yahan virtually hug bhej raha hoon 🤗 tu theek ho jayega',
                'Jo bhi ho raha hai, tu usse bada hai — dil se bol raha hoon 🔥🫶',
                'Soft day choose kar — hustle kabhi aur kar lena 🧸',
            ],
            'happy' => [
                'Aaj ka din tera hai 🔥 enjoy kar 😎',
                'Energy dekhi? Full main character vibes ✨👑',
                'Mood 100/100 lag raha hai — celebrate small wins bhi 🎉',
                'Tu glow kar raha hai literally ✨📸 selfie le le',
                'Happy ho to world ko bata de — good vibes contagious hain 🌍💛',
                'Dance break mandatory hai ab 🕺💃 volume up!',
                'Aaj treat yourself — tum deserve karte ho 🍦🛍️',
                'Yeh energy bottle karke rakh le, low days ke liye 🔋😄',
                'Smile se screen bright ho gayi 😁📱 keep shining',
                'Main bol raha hoon: aaj tu main character ho 📽️🍿',
                'Happy + chai = unbeatable combo ☕✨',
                'Good news sunke maza aa gaya — aur share kar 🎊',
                'Tu lucky vibe carry kar raha hai 🍀 ride it!',
                'Hasna band mat — ye look best hai tere pe 😂💖',
                'Aaj goals crush kar, par fun bhi miss mat kar 🎯🎮',
                'Sunshine person energy ☀️ thank you for existing',
                'Party mode ON? Mere hisaab se ON hi rehna 🎈🎶',
                'Khushi share kar — group chat mein blast kar de 💬🚀',
                'Aaj gratitude note likh — mood aur boost hoga 📝💫',
                'Happy ho? Water bhi pi le, hydration queen/king 👑💧',
                'Zindagi ne aaj green signal diya hai 🚦 full speed (safe) 🏎️',
                'Vibe check: passed with flying colours 🌈✅',
            ],
            'stressed' => [
                'Ek kaam ek time pe — multitasking ko thoda rest de 🧘',
                'Deep breath in… out… ab thoda better? 🌬️💙',
                'Tu kar loge, bas pace apna set kar 🐢✅ slow is fine',
                'Chaos ko list mein tod de — brain instant relief 📝🧠',
                '2 min walk le ke aa — perspective shift ho jata hai 🚶‍♂️🌿',
                'Stress ko name do: “ye temporary hai” 🔖 calm',
                'Phone side pe, paani pi, phir tackle kar 📵💧',
                'Perfect hone ki zaroorat nahi, done hone ki hai ✔️🙌',
                'Shoulders drop kar — tension store mat kar 🫳',
                'Aaj sirf top 3 tasks — baaki kal ke liye bookmark 📌',
                'Tu overloaded lag raha hai — ek cheez delegate ho sakti? 🤝',
                'Nature ka 5 min = mental refresh 🌳 recharge',
                'Stretch kar — body relax to mind follow karti hai 🙆‍♀️',
                'Panic mode mein: 4-7-8 breathing try kar 🫁✨',
                'Progress chhota ho to bhi progress hai — note kar 🌱',
                'Noise cancel headphones = focus hack 🎧🔒',
                'Break lena productive hai, lazy nahi ☕🧩',
                'Tu capable hai, bas oxygen ki shortage lag rahi hai — breathe 🌬️',
                'Deadline scary hai par tu prepared hai zyada than you think 💪📅',
                'Ek grateful thought soch — nervous system ko signal achha jata hai 🙏💚',
                'Micro-step le: bas 5 min start — momentum build ho jayega ⚡',
                'Brain fog? Protein snack + paani — sometimes hangry hai tu 🥜💧',
            ],
            'angry' => [
                'Pause le — reply se pehle 10 sec 🛑 cool down',
                'Gussa valid hai, bas reaction choose kar wisely 🎯',
                'Walk pe nikal ja — fire thoda kam ho jati hai 🚶‍♂️🔥',
                'Jo control nahi usko chhod de — energy save kar 🧘',
                'Ice water pi — body calm to mind follow 💧🧊',
                'Journal mein likh jo bolna chahta hai — safe outlet 📓✍️',
                'Anger = signal, identity nahi — separate kar 🧠',
                'Music switch kar — tempo slow wala 🎵😌',
                'Boundary set karna seekh — anger often = unmet need 🚧',
                'Boxing pillow = underrated therapy 🥊🛏️',
                'Deep breath: gussa fuel hai, burn mat hone de 🌬️',
                'Social media break — trigger kam honge 📵',
                'Tu theek hai, situation tight hai — difference samajh 🫶',
                'Count backwards 10 se 1 — classic but works 🔢',
                'Jo hua so hua, ab next best move soch ♟️',
                'Nature + silence = reset button 🌲🤫',
                'Friend ko call kar — vent out, explode mat 💬',
                'Stretch + jaw release — tension hide karti hai jaw mein 😮‍💨',
                'Aaj revenge mode off, recover mode on 🔄💚',
                'Tu zyada bada hai apni feelings se — ride the wave 🌊',
                'Ground yourself: 5 cheezein dikhti hain? naam le 👀🌍',
                'Cooling tea pi — gussa thoda melt ho jata hai 🍵❄️',
            ],
        ];
    }
}
