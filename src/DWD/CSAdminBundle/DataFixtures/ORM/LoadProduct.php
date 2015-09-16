<?php

namespace DWD\CSAdminBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use DWD\CSAdminBundle\Entity\Product;
use DWD\CSAdminBundle\Entity\Category;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadProduct implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */

    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $category = new Category();
        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('诗泥东南亚SPA套餐');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('诗泥东南亚SPA套餐1');
        $product->setPrice(9.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('招牌腊味煲仔饭(限堂食)');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $product = new Product();

        $category->setName('Main Products');
        $product->setName('Foo');
        $product->setPrice(19.99);
        $product->setDescription('Lorem ipsum dolor');
        // relate this product to the category
        $product->setCategory($category);
        $manager->persist($product);

        $manager->flush();
    }
}