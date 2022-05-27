<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Carrier;
use App\Entity\Product;
use App\Entity\Category;
use App\Controller\Admin\OrderCrudController;
use App\Entity\Header;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($routeBuilder->setController(OrderCrudController::class)->generateUrl());
        
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('La Boutique')
            ->setFaviconPath('home.svg');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('utilisateurs', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Orders', 'fa fa-shopping-cart', Order::class);
        yield MenuItem::linkToCrud('categories', 'fa fa-list', Category::class);
        yield MenuItem::linkToCrud('products', 'fa fa-tag', Product::class);
        yield MenuItem::linkToCrud('carriers', 'fa fa-truck', Carrier::class);
        yield MenuItem::linkToCrud('header', 'fa fa-desktop', Header::class);


    }
}
