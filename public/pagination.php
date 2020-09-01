<h2>Pagination</h2>

<?php
use SemelaPavel\Pagination\Pagination;

$numOfItems = 100;
$itemsPerPage = 5;

$options = array('options' => array('default'=> 5)); 
$currentPage = filter_input(INPUT_GET, 'page-number', FILTER_VALIDATE_INT, $options);

$pagination = new Pagination($numOfItems, $itemsPerPage, $currentPage);

$page = filter_input(INPUT_GET, 'page');
$pageNumberStr = "?page={$page}&amp;page-number=";
?>


<ul class="pagination">
    
    <?php if ($pagination->getPrevPage()): ?>
    
        <li><a href="<?= $pageNumberStr . $pagination->getPrevPage() ?>">&laquo; Previous</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>&laquo; Previous</span></li>
        
    <?php endif; ?>
    
<?php
foreach ($pagination->toArray(1, 2) as $page):
    
    if ($page['page'] !== null):
        if ($page['isCurrent']): ?>
            
            <li class="active"><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php else: ?>
            
            <li><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php endif; else: ?>

        <li class="disabled"><span>...</span></li>
        
    <?php endif; 
endforeach; ?>    
    
    <?php if ($pagination->getNextPage()): ?>
        
        <li><a href="<?= $pageNumberStr . $pagination->getNextPage() ?>">Next &raquo;</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>Next &raquo;</span></li>
        
    <?php endif; ?>
</ul>


<br>
<ul class="pagination">
    
    <?php if ($pagination->getPrevPage()): ?>
    
        <li><a href="<?= $pageNumberStr . $pagination->getPrevPage() ?>">&laquo; Previous</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>&laquo; Previous</span></li>
        
    <?php endif; ?>
    
<?php
foreach ($pagination->toArray(2, 2) as $page):
    
    if ($page['page'] !== null):
        if ($page['isCurrent']): ?>
            
            <li class="active"><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php else: ?>
            
            <li><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php endif; else: ?>

        <li class="disabled"><span>...</span></li>
        
    <?php endif; 
endforeach; ?>    
    
    <?php if ($pagination->getNextPage()): ?>
        
        <li><a href="<?= $pageNumberStr . $pagination->getNextPage() ?>">Next &raquo;</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>Next &raquo;</span></li>
        
    <?php endif; ?>
</ul>


<br>
<ul class="pagination">
    
    <?php if ($pagination->getPrevPage()): ?>
    
        <li><a href="<?= $pageNumberStr . $pagination->getPrevPage() ?>">&laquo; Previous</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>&laquo; Previous</span></li>
        
    <?php endif; ?>
    
<?php
foreach ($pagination->toArray(0, 2) as $page):
    
    if ($page['page'] !== null):
        if ($page['isCurrent']): ?>
            
            <li class="active"><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php else: ?>
            
            <li><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php endif; else: ?>

        <li class="disabled"><span>...</span></li>
        
    <?php endif; 
endforeach; ?>    
    
    <?php if ($pagination->getNextPage()): ?>
        
        <li><a href="<?= $pageNumberStr . $pagination->getNextPage() ?>">Next &raquo;</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>Next &raquo;</span></li>
        
    <?php endif; ?>
</ul>


<br>
<ul class="pagination">
    
    <?php if ($pagination->getPrevPage()): ?>
    
        <li><a href="<?= $pageNumberStr . $pagination->getPrevPage() ?>">&laquo; Previous</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>&laquo; Previous</span></li>
        
    <?php endif; ?>
    
<?php
foreach ($pagination->toArray(1, 0) as $page):
    
    if ($page['page'] !== null):
        if ($page['isCurrent']): ?>
            
            <li class="active"><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php else: ?>
            
            <li><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php endif; else: ?>

        <li class="disabled"><span>...</span></li>
        
    <?php endif; 
endforeach; ?>    
    
    <?php if ($pagination->getNextPage()): ?>
        
        <li><a href="<?= $pageNumberStr . $pagination->getNextPage() ?>">Next &raquo;</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>Next &raquo;</span></li>
        
    <?php endif; ?>
</ul>


<br>
<ul class="pagination">
    
    <?php if ($pagination->getPrevPage()): ?>
    
        <li><a href="<?= $pageNumberStr . $pagination->getPrevPage() ?>">&laquo; Previous</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>&laquo; Previous</span></li>
        
    <?php endif; ?>
    
<?php
foreach ($pagination->toArray(0, 0) as $page):
    
    if ($page['page'] !== null):
        if ($page['isCurrent']): ?>
            
            <li class="active"><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php else: ?>
            
            <li><a href="<?= $pageNumberStr . $page['page'] ?>"><?= $page['page'] ?></a></li>
            
        <?php endif; else: ?>

        <li class="disabled"><span>...</span></li>
        
    <?php endif; 
endforeach; ?>    

    <?php if ($pagination->getNextPage()): ?>
        
        <li><a href="<?= $pageNumberStr . $pagination->getNextPage() ?>">Next &raquo;</a></li>
        
    <?php else: ?>
        
        <li class="disabled"><span>Next &raquo;</span></li>
        
    <?php endif; ?>
</ul>
