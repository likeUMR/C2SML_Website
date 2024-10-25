<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>满屏幕天降彩带效果测试</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <style>
        /* 让页面居中 */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f3f4f6; /* Tailwind 的灰色背景 */
        }
    </style>
</head>
<body>
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">满屏幕天降彩带效果测试</h1>
        <button id="confettiBtn" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            点击我放满屏彩带 🎉
        </button>
    </div>

    <script>
        // 绑定按钮点击事件
        document.getElementById('confettiBtn').addEventListener('click', function() {
            // 触发满屏幕天降彩带效果
            for (let i = 0; i < 5; i++) {  // 连续触发多次彩带效果，模拟满屏彩带
                setTimeout(function() {
                    confetti({
                        particleCount: 200,  // 每次200个粒子
                        startVelocity: 30,   // 初始速度
                        spread: 360,         // 360度扩散
                        ticks: 60,           // 粒子存活时间
                        origin: {
                            x: Math.random(), // 随机横向位置
                            y: Math.random() - 0.2 // 随机纵向位置稍微偏上
                        },
                        colors: ['#bb0000', '#ffffff', '#00bb00', '#0000bb', '#ffbb00']  // 彩带颜色
                    });
                }, i * 200); // 每隔200毫秒触发一次
            }
        });
    </script>
</body>
</html>
