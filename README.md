### Laravel permission
该库主要功能为拓展 User 的权限控制，包括用户组权限控制以及功能权限控制


#### 使用方法

1. 在 `composer.json` 中添加仓库地址

    ```json
    {
    "repositories": [
            {
                "type": "vcs",
                "url": "https://gitee.com/padakeji/laravel-permission.git"
            }
        ]
    }
    ```
   
2. 在项目中加入包
    
    ```bash
   composer require jobsys/permission
    ```

3. 执行初始化

   ```bash
   -- 默认会执行全部初始化，如需指定某部分内加上 `tag` 选项 
   php artisan vendor:publish --provider="Jobsys\Permission\PermissionServiceProvider"
   
   php artisan vendor:publish --provider="Jobsys\Permission\PermissionServiceProvider" --tag="config"
   php artisan vendor:publish --provider="Jobsys\Permission\PermissionServiceProvider" --tag="migrations"
   php artisan vendor:publish --provider="Jobsys\Permission\PermissionServiceProvider" --tag="views"
   
   ```
   
4. 生成数据库表

    ```bash
    php artisan migrate
    ```


> `permissions` 中的 `key` 形式如 `api.manager.user`, `api.manager.user.edit`, `api.manager.user.*`, 根据项目具体情况生成插入即可

 
5. 为 User 添加权限相关方法

    ```php
    
    class User extends Authenticatable
    {
        use HasPermissions, HasRoles;
        ...
    }
    ``` 
   
6. 配置项 `config/permission.php`

|配置项|类型|说明|
|--|--|--|
|enable_wildcard_permission|Boolean|是否开启权限通配检测|
|preset_super_groups|Array|预设的超级用户权限组，值为 Role 的 key|
