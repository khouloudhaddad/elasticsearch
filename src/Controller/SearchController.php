<?php

namespace App\Controller;

use App\Form\Model\SearchModel;
use App\Form\Type\SearchFormType;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchPhrase;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{

    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly PaginatedFinderInterface $finder
    )
    {
    }

    #[Route('/search', name: 'app_search')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var SearchModel $data */
            $data = new SearchModel($form->getData()['query'], $form->getData()['category'], $form->getData()['createdThisMonth']);
            //$data = $form->getData();

            $page = $request->query->getInt('page', 1);
            $boolQuery = new BoolQuery();

            if($data->query){
                $boolQuery->addMust(new MatchPhrase('title', $data->query));
            }

            $results = $this->finder->createPaginatorAdapter($boolQuery);
            $pagination = $this->paginator->paginate($results, $page);

        }

        return $this->render('search/index.html.twig',
        [
            'form' => $form->createView(),
            'pagination' => $pagination ?? []
        ]);
    }
}
