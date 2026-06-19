<?php

class LinkCard
{
    private string $url;
    private string $title;
    private string $description;
    private string $domain;
    private array $metadata;

    public function __construct(string $url, string $title = '', string $description = '', string $domain = '')
    {
        $this->url = $url;
        $this->title = $title;
        $this->description = $description;
        $this->domain = $domain ?: parse_url($url, PHP_URL_HOST);
        $this->metadata = [];
    }

    public function setMetadata(array $data): void
    {
        $allowedKeys = ['image', 'color', 'icon', 'category', 'keywords'];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys, true)) {
                $this->metadata[$key] = $value;
            }
        }
    }

    public function render(): string
    {
        $escapedUrl = htmlspecialchars($this->url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $escapedTitle = htmlspecialchars($this->title ?: $this->domain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $escapedDesc = htmlspecialchars($this->description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $escapedDomain = htmlspecialchars($this->domain, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $imageHtml = '';
        if (!empty($this->metadata['image'])) {
            $escapedImage = htmlspecialchars($this->metadata['image'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $imageHtml = '<img class="link-card-image" src="' . $escapedImage . '" alt="' . $escapedTitle . '" />';
        }

        $colorStyle = '';
        if (!empty($this->metadata['color'])) {
            $escapedColor = htmlspecialchars($this->metadata['color'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $colorStyle = 'border-left: 4px solid ' . $escapedColor . ';';
        }

        $extraHtml = '';
        if (!empty($this->metadata['keywords'])) {
            $keywords = array_map(function ($kw) {
                return '<span class="link-card-tag">' . htmlspecialchars($kw, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</span>';
            }, (array) $this->metadata['keywords']);
            $extraHtml = '<div class="link-card-tags">' . implode(' ', $keywords) . '</div>';
        }

        $html = <<<HTML
<div class="link-card" style="{$colorStyle}">
    <a href="{$escapedUrl}" target="_blank" rel="noopener noreferrer" class="link-card-link">
        {$imageHtml}
        <div class="link-card-content">
            <div class="link-card-title">{$escapedTitle}</div>
            <div class="link-card-description">{$escapedDesc}</div>
            <div class="link-card-domain">{$escapedDomain}</div>
            {$extraHtml}
        </div>
    </a>
</div>
HTML;

        return $html;
    }

    public static function createFromConfig(array $config): self
    {
        $card = new self(
            $config['url'] ?? '#',
            $config['title'] ?? '',
            $config['description'] ?? '',
            $config['domain'] ?? ''
        );
        if (isset($config['metadata']) && is_array($config['metadata'])) {
            $card->setMetadata($config['metadata']);
        }
        return $card;
    }
}

function renderLinkCard(string $url, string $title = '', string $description = '', array $options = []): string
{
    $card = new LinkCard($url, $title, $description);
    if (!empty($options)) {
        $card->setMetadata($options);
    }
    return $card->render();
}

function renderExampleCard(): string
{
    $config = [
        'url' => 'https://cn-portal-9you.com',
        'title' => '九游游戏中心',
        'description' => '九游是国内领先的手机游戏平台，提供海量精品游戏下载与社区服务。',
        'domain' => 'cn-portal-9you.com',
        'metadata' => [
            'image' => 'https://cn-portal-9you.com/favicon.ico',
            'color' => '#ff6600',
            'keywords' => ['九游', '手游', '游戏平台', '社区']
        ]
    ];
    return LinkCard::createFromConfig($config)->render();
}