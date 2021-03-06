<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BreadcrumbsGenerator.
 */
class BreadcrumbsGenerator
{
    const MENU = [
        'a-propos' => 'À propos',
        'event_posts_list' => 'Évènements',
        'news_posts_list' => 'Actualités',
        'especes' => 'Espèces à Observer',
        'participer' => 'Comment participer',
        'my_stations' => 'Saisir mes données',
        'resultats' => 'Résultats',
        'outils-ressources' => 'Outils & ressources',
        'relais' => 'Relais',
    ];
    const OTHER_BREADCRUMBS = [
        'stations' => 'Stations d’observation',
        'stations_search' => 'Recherche de stations',
        'my_stations' => 'Mes stations',
        'protocole' => 'Protocole',
        'user_dashboard' => 'Tableau de bord',
        'user_profile' => 'Profil de l’utilisateur',
    ];
    const EDITABLE_PAGES = [
        'a-propos',
        'participer',
        'protocole',
        'resultats',
        'outils-ressources',
        'relais',
    ];

    protected RequestStack $requestStack;
    private array $trails;
    private array $activeTrail;
    private string $removeStringFromPath;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->trails = [];
        $this->activeTrail = [];
        $this->removeStringFromPath = '';
    }

    public function setToRemoveFromPath(string $string)
    {
        $this->removeStringFromPath = $string;

        return $this;
    }

    private function buildTrail(string $route, string $label)
    {
        return [
            'route' => urldecode($route),
            'label' => $label,
        ];
    }

    public function addTrail(string $route, string $label)
    {
        $this->trails[] = $this->buildTrail($route, $label);
    }

    public function getTrails(): array
    {
        $breadcrumbs = [];
        foreach ($this->trails as $trail) {
            $breadcrumbs[$trail['route']] = $trail['label'];
        }

        return $breadcrumbs;
    }

    public function buildBreadcrumbs(array $routes): array
    {
        $pageBreadcrumbs = array_merge(self::MENU, self::OTHER_BREADCRUMBS);
        foreach ($routes as $route) {
            $this->addTrail($route, $pageBreadcrumbs[$route] ?? $route);
        }

        if (!empty($this->activeTrail)) {
            $this->addTrail($this->activeTrail['route'], $this->activeTrail['label']);
        }

        return $this->getTrails();
    }

    private function getRoutes(string $urlInfos): array
    {
        return array_filter(explode('/', $urlInfos));
    }

    public function setActiveTrail(string $route = null, string $label = null): self
    {
        if (!$route) {
            $route = $this->requestStack->getCurrentRequest()
                ->attributes->get('_route');
        }

        $label = $label ?? array_merge(BreadcrumbsGenerator::MENU, BreadcrumbsGenerator::OTHER_BREADCRUMBS)[$route] ?? $route;

        $this->activeTrail = $this->buildTrail($route, $label);

        return $this;
    }

    /**
     * @return array
     */
    public function getBreadcrumbs(string $urlInfos = null)
    {
        $request = $this->requestStack->getCurrentRequest();

        $pathInfo = $request->getPathInfo();
        if (!empty($this->removeStringFromPath)) {
            $pathInfo = str_replace($this->removeStringFromPath, '', $pathInfo);
        }

        if (!$urlInfos) {
            if (1 === count($this->getRoutes($pathInfo))) {
                return $this->buildBreadcrumbs(
                    [$request->attributes->get('_route')]
                );
            }

            $urlInfos = $pathInfo;
        }

        // remove slug part of the called page url (bc slug contains slash)
        if (!empty($this->activeTrail)) {
            $urlInfos = str_replace($this->activeTrail['route'], '', $urlInfos);
        }

        return $this->buildBreadcrumbs($this->getRoutes($urlInfos));
    }
}
