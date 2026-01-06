My implementation to limit a certain "package" to 1 active at a time. As i couldnt figure out how to get settings to work, You need to update the following inside of src\EventListener\FreePlanServerLimitListener.php


private array $freeProductIds = [1];

private int $maxFreeServers = 1; 


You can get the ID, or IDs, from editing your product and looking at the end of the URL. $freeProductIds can take multiple i.e [1,4,7]
panel?crudAction=edit&crudControllerFqcn=App\Core\Controller\Panel\ProductCrudController&entityId=1
