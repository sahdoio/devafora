<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $post->title }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #0f172a;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #0f172a;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: 0 auto; max-width: 600px;">

                    <!-- Header with Logo -->
                    <tr>
                        <td style="text-align: center; padding-bottom: 30px;">
                            <img src="https://devafora.com/images/logo_blue.png" alt="DevAfora" style="width: 180px; height: auto; display: block; margin-left: auto; margin-right: auto;">
                        </td>
                    </tr>

                    <!-- New Post Badge -->
                    <tr>
                        <td style="text-align: center; padding-bottom: 20px;">
                            <span style="display: inline-block; background-color: #1e40af; color: #bfdbfe; padding: 8px 20px; border-radius: 20px; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                ✨ Novo Post Publicado
                            </span>
                        </td>
                    </tr>

                    <!-- Featured Image (if exists) -->
                    @if($imageUrl)
                    <tr>
                        <td style="padding-bottom: 20px;">
                            <img src="{{ $imageUrl }}" alt="{{ $post->title }}" style="width: 100%; height: auto; border-radius: 12px; display: block;">
                        </td>
                    </tr>
                    @endif

                    <!-- Content Card -->
                    <tr>
                        <td style="background-color: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 40px 30px;">
                            <!-- Title -->
                            <h1 style="font-size: 28px; font-weight: bold; margin: 0 0 20px; color: #f1f5f9; line-height: 1.3;">
                                {{ $post->title }}
                            </h1>

                            <!-- Meta Information -->
                            <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #334155;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td style="padding-right: 15px;">
                                            <span style="color: #60a5fa; font-size: 14px; font-weight: 500;">👤 {{ $post->author }}</span>
                                        </td>
                                        @if($post->read_time)
                                        <td style="padding-right: 15px;">
                                            <span style="color: #94a3b8; font-size: 14px;">⏱️ {{ $post->read_time }} min de leitura</span>
                                        </td>
                                        @endif
                                        <td>
                                            <span style="color: #94a3b8; font-size: 14px;">📅 {{ $post->published_at->format('d/m/Y') }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Excerpt -->
                            <p style="font-size: 17px; line-height: 1.7; color: #cbd5e1; margin: 0 0 30px;">
                                {{ $post->excerpt }}
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center; padding: 10px 0;">
                                        <a href="{{ $url }}" style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 10px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.4);">
                                            📖 Ler Post Completo
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Tags -->
                            @if($post->tags && count($post->tags) > 0)
                            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #334155;">
                                <p style="font-size: 14px; color: #94a3b8; margin: 0 0 10px; font-weight: 500;">
                                    🏷️ Tags:
                                </p>
                                <div>
                                    @foreach($post->tags as $tag)
                                    <span style="display: inline-block; background-color: #1e40af; color: #bfdbfe; padding: 6px 14px; border-radius: 6px; font-size: 13px; margin: 4px 4px 4px 0; font-weight: 500;">
                                        {{ $tag }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>

                    <!-- What's Next Section -->
                    <tr>
                        <td style="background-color: #0f172a; border: 1px solid #1e3a8a; border-radius: 12px; padding: 25px; margin-top: 20px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center;">
                                        <h3 style="font-size: 18px; color: #60a5fa; margin: 0 0 15px; font-weight: 600;">
                                            💡 Continue Aprendendo
                                        </h3>
                                        <p style="font-size: 15px; color: #94a3b8; margin: 0 0 15px; line-height: 1.6;">
                                            Não perca os próximos posts! Continue acompanhando o DevAfora para mais conteúdos sobre desenvolvimento web.
                                        </p>
                                        <a href="https://devafora.com" style="color: #60a5fa; text-decoration: none; font-size: 15px; font-weight: 500;">
                                            Visite o site →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="text-align: center; padding-top: 30px; border-top: 1px solid #334155; margin-top: 30px;">
                            <!-- Social Links -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 20px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="https://youtube.com/@devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 500;">YouTube</a>
                                        <span style="color: #475569;">•</span>
                                        <a href="https://tiktok.com/@devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 500;">TikTok</a>
                                        <span style="color: #475569;">•</span>
                                        <a href="https://twitter.com/devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 500;">Twitter</a>
                                        <span style="color: #475569;">•</span>
                                        <a href="https://instagram.com/devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 500;">Instagram</a>
                                        <span style="color: #475569;">•</span>
                                        <a href="https://github.com/devafora" style="display: inline-block; margin: 0 10px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 500;">GitHub</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; color: #64748b; margin: 10px 0;">
                                <strong style="color: #94a3b8;">DevAfora</strong> - Desenvolvimento Web de Qualidade<br>
                                <a href="https://devafora.com" style="color: #3b82f6; text-decoration: none;">devafora.com</a>
                            </p>

                            <p style="margin-top: 20px; font-size: 12px; color: #64748b; line-height: 1.6;">
                                Você está recebendo este e-mail porque se inscreveu na newsletter do DevAfora.<br>
                                Se deseja parar de receber estes e-mails, entre em contato conosco.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
