<?php

declare(strict_types=1);

namespace App\Controller;
use App\Repository\CursoRepository;
use App\Repository\CategoriaRepository;
use App\Model\Curso;


use Exception;


class CursoController extends AbstractController
{
    private CursoRepository $repository;

    public function __construct()
    {
        $this->repository = new CursoRepository;
    }



    public function listar() : void
    {
        $cursos = $this->repository->buscarTodos();

        $this->render('curso/listar', [
            'cursos' => $cursos,
        ]);
    }

    public function cadastrar(): void
    {
        if(true === empty($_POST)){
            $this->categoriaRepository = new CategoriaRepository;
            $this->render('curso/cadastrar', [
                'categorias' => $this->categoriaRepository->buscarTodos()
        ]);
            return;
        }
        
        $curso = new Curso();
        $curso->nome = $_POST['nome'];
        $curso->cargaHoraria = $_POST['cargaHoraria'];
        $curso->descricao = $_POST['descricao'];
        $curso->categoria_id = intval($_POST['categoria']);
        
        try{
            $this->repository->inserir($curso);
        } catch(Exception $exception){
            if(str_contains($exception->getMessage(), 'nome')){
                die('O curso já existe');
            }

            die('Vish, aconteceu um erro');
        }
        $this->redirect('/cursos/listar');      
    }

    public function excluir() : void
    {
        $id = $_GET['id'];
        $this->repository->excluir($id);
        
        $this->redirect('/cursos/listar');
    }

    public function editar() : void
    {
        $id = $_GET['id'];
        $curso = $this->repository->buscarUm($id);
        $this->categoriaRepository = new CategoriaRepository;
        $this->render('curso/editar', [
            $curso,
            'categorias' => $this->categoriaRepository->buscarTodos()
        ]); 
        if(false === empty($_POST)){

            $curso->nome = $_POST['nome'];
            $curso->cargaHoraria = $_POST['cargaHoraria'];
            $curso->descricao = $_POST['descricao'];
            $curso->categoria_id = intval($_POST['categoria']);

            try{
                $this->repository->atualizar($curso, $id);
            } catch(Exception $exception){
                if(str_contains($exception->getMessage(), 'nome')){
                    die('O curso já existe');
                }

                die('Vish, aconteceu um erro');
            }
            $this->redirect('/cursos/listar');
        }
    }
}