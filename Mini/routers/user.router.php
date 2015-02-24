<?php

$app->group('/user', function () use ($app, $model) {
    
    $user_model = new \Mini\Model\Users($app->config('database'));
    $role_model = new \Mini\Model\Role($app->config('database'));
    
    $app->get('/', 'authen', function () use ($app, $model) {
        
//        $user = validate();
//        
//        if ($user->level == 1) {
//            $app->flash('error', 'Invalid access level');
//            $app->redirect('/');
//        }
        
//        $users = $model->get_users();
        $app->render('user/index.twig', array(
            'users' => $users,
            'error' => getFlash('error'),
        ));
    });

    $app->post('/login', function () use ($app, $model) {
        $login = $model->login_user($app);

        // If can not login
        if ($login === false) {
            $app->flash('error', 'Invalid username or password :(');
            $app->redirect('/login');
        }else{
            $app->setCookie('user', $login['id'], '1 day');
            $_SESSION['user'] = $login;
            $app->redirect('/');
        }
    });

    $app->get('/logout', function () use ($app) {
        unset($_SESSION['user']);
        $app->setCookie('user', null, -1);
        $app->view()->setData('user', null);
        $app->redirect('/');
    });
    
    $app->get('/perms', 'authen', function () use ($app, $user_model) {
        
        $items = $user_model->gets();
        $app->render('users/index.twig', array(
            'items' => $items,
        ));
        
    });
    
    $app->get('/form/:id', 'authen', function ($id) use ($app, $user_model, $role_model) {
        
        $item = $user_model->get($id); // Get user detail
        $groups = $user_model->getGroups(); // Get group
        $roles = $role_model->getRolePerms(); // Get role_perm
        
        $app->render('users/form.twig', array(
            'item' => $item,
            'groups' => $groups,
            'roles' => $roles,
            'id' => $id
        ));
        
    });
    
    $app->post('/save', 'authen', function () use ($app, $user_model) {
        try {
            $user_model->save();
            $app->redirect('/user/perms');
            
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
    });
    
    $app->get('/form_pass', 'authen', function () use ($app, $user_model) {
        
        $items = $user_model->gets(); // Get users detail
        $app->render('users/form_pass.twig', array(
            'items' => $items,
            'id' => 0
        ));
        
    });
    
    $app->post('/save_pass', 'authen', function () use ($app, $user_model) {
        try {
            $user_model->save_pass($app->config('salt'));
            $app->redirect('/user/form_pass');
            
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
    });
    
});