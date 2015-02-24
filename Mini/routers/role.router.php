<?php
$app->group('/role', function () use ($app) {
    
    $role_model = new \Mini\Model\Role($app->config('database'));
    $perm_model = new \Mini\Model\Permission($app->config('database'));
    
    $app->get('/', 'authen', function () use ($app, $role_model) {

        $items = $role_model->getRolePerms();
        $app->render('role/index.twig', array(
            'items' => $items,
        ));
    });

    $app->get('/form', 'authen', function () use ($app, $role_model, $perm_model) {
        $roles = $role_model->gets();
        $perms = $perm_model->gets();

        $app->render('role/form.twig', array(
            'id' => 0,
            'roles' => $roles,
            'perms' => $perms,
            'item' => null,
        ));
    });
    
    $app->get('/form/:id', 'authen', function ($id) use ($app, $role_model, $perm_model) {
        
        $roles = $role_model->gets();  // Get roles
        $perms = $perm_model->gets();  // Get permission
        $item = $role_model->get($id); // Get current role_perm from id
        
        $app->render('role/form.twig', array(
            'id' => $id,
            'roles' => $roles,
            'perms' => $perms,
            'item' => $item,
        ));
    });
    
    $app->post('/save', 'authen', function () use ($app, $role_model) {
        try {
            $role_model->save();
            $app->redirect('/role');
            
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    });

});