<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo à Newsletter</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            color: white;
        }
        h1 {
            font-size: 28px;
            font-weight: bold;
            margin: 0 0 10px;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .subtitle {
            font-size: 16px;
            color: #94a3b8;
            margin: 0;
        }
        .content {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #f1f5f9;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
        .benefits {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        .benefits li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: #e2e8f0;
        }
        .benefits li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #3b82f6;
            font-weight: bold;
            font-size: 18px;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            transition: all 0.3s;
        }
        .footer {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(59, 130, 246, 0.2);
            color: #64748b;
            font-size: 14px;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">D</div>
            <h1>Bem-vindo ao DevAfora! 🎉</h1>
            <p class="subtitle">Você acaba de se juntar a uma comunidade incrível</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">
                Olá{{ $subscription->name ? ', ' . $subscription->name : '' }}!
            </p>

            <p class="message">
                É com grande alegria que damos as boas-vindas à newsletter do <strong>DevAfora</strong>!
                Você tomou uma decisão excelente ao se juntar a nós.
            </p>

            <p class="message">
                A partir de agora, você receberá conteúdos exclusivos diretamente na sua caixa de entrada:
            </p>

            <ul class="benefits">
                <li>Tutoriais práticos sobre Laravel, Vue.js e tecnologias modernas</li>
                <li>Dicas e truques para melhorar sua produtividade</li>
                <li>Novidades sobre arquitetura de software e boas práticas</li>
                <li>Conteúdos exclusivos que não são publicados em nenhum outro lugar</li>
                <li>Atualizações sobre novos posts e recursos</li>
            </ul>

            <p class="message">
                Enquanto isso, que tal conhecer nossos últimos posts?
            </p>

            <center>
                <a href="https://devafora.com" class="button">
                    Visite o Blog →
                </a>
            </center>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="social-links">
                <a href="https://youtube.com/@devafora">YouTube</a>
                <a href="https://tiktok.com/@devafora">TikTok</a>
                <a href="https://twitter.com/devafora">Twitter</a>
                <a href="https://instagram.com/devafora">Instagram</a>
                <a href="https://github.com/devafora">GitHub</a>
            </div>

            <p>
                DevAfora - Desenvolvimento Web de Qualidade<br>
                <a href="https://devafora.com">devafora.com</a>
            </p>

            <p style="margin-top: 20px; font-size: 12px;">
                Você está recebendo este e-mail porque se inscreveu na nossa newsletter.<br>
                E-mail enviado para: {{ $subscription->email }}
            </p>
        </div>
    </div>
</body>
</html>
