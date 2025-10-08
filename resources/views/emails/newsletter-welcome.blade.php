<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Bem-vindo à Newsletter</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #0f172a;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #0f172a;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: 0 auto; max-width: 600px;">

                    <!-- Header with Logo -->
                    <tr>
                        <td style="text-align: center; padding-bottom: 40px;">
                            <img src="https://devafora.com/images/logo_blue.png" alt="DevAfora" style="width: 200px; height: auto; margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto;">
                            <h1 style="font-size: 28px; font-weight: bold; margin: 20px 0 10px; color: #60a5fa;">
                                Bem-vindo ao DevAfora! 🎉
                            </h1>
                            <p style="font-size: 16px; color: #94a3b8; margin: 0;">
                                Você acaba de se juntar a uma comunidade incrível
                            </p>
                        </td>
                    </tr>

                    <!-- Content Card -->
                    <tr>
                        <td style="background-color: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 30px;">
                            <p style="font-size: 18px; margin: 0 0 20px; color: #f1f5f9; font-weight: 500;">
                                Olá{{ $subscription->name ? ', ' . $subscription->name : '' }}!
                            </p>

                            <p style="font-size: 16px; line-height: 1.6; color: #cbd5e1; margin: 0 0 20px;">
                                É com grande alegria que damos as boas-vindas à newsletter do <strong style="color: #60a5fa;">DevAfora</strong>!
                                Você tomou uma decisão excelente ao se juntar a nós.
                            </p>

                            <p style="font-size: 16px; line-height: 1.6; color: #cbd5e1; margin: 0 0 20px;">
                                A partir de agora, você receberá conteúdos exclusivos diretamente na sua caixa de entrada:
                            </p>

                            <!-- Benefits List -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 20px 0;">
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <span style="color: #3b82f6; font-weight: bold; font-size: 18px; margin-right: 10px;">✓</span>
                                        <span style="color: #e2e8f0; font-size: 16px;">Tutoriais práticos sobre Laravel, Vue.js e tecnologias modernas</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <span style="color: #3b82f6; font-weight: bold; font-size: 18px; margin-right: 10px;">✓</span>
                                        <span style="color: #e2e8f0; font-size: 16px;">Dicas e truques para melhorar sua produtividade</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <span style="color: #3b82f6; font-weight: bold; font-size: 18px; margin-right: 10px;">✓</span>
                                        <span style="color: #e2e8f0; font-size: 16px;">Novidades sobre arquitetura de software e boas práticas</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <span style="color: #3b82f6; font-weight: bold; font-size: 18px; margin-right: 10px;">✓</span>
                                        <span style="color: #e2e8f0; font-size: 16px;">Conteúdos exclusivos que não são publicados em nenhum outro lugar</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <span style="color: #3b82f6; font-weight: bold; font-size: 18px; margin-right: 10px;">✓</span>
                                        <span style="color: #e2e8f0; font-size: 16px;">Atualizações sobre novos posts e recursos</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 16px; line-height: 1.6; color: #cbd5e1; margin: 20px 0;">
                                Enquanto isso, que tal conhecer nossos últimos posts?
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="text-align: center; padding-top: 30px; border-top: 1px solid #334155; margin-top: 30px;">
                            <!-- Social Links -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 20px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="https://youtube.com/@devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px;">YouTube</a>
                                        <a href="https://tiktok.com/@devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px;">TikTok</a>
                                        <a href="https://twitter.com/devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px;">Twitter</a>
                                        <a href="https://instagram.com/devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px;">Instagram</a>
                                        <a href="https://github.com/devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px;">GitHub</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; color: #64748b; margin: 10px 0;">
                                DevAfora - Desenvolvimento Web de Qualidade<br>
                                <a href="https://devafora.com" style="color: #3b82f6; text-decoration: none;">devafora.com</a>
                            </p>

                            <p style="margin-top: 20px; font-size: 12px; color: #64748b;">
                                Você está recebendo este e-mail porque se inscreveu na nossa newsletter.<br>
                                E-mail enviado para: {{ $subscription->email }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
