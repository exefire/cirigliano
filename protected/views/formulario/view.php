<?php
/* @var $this FormularioController */
/* @var $model Formulario */
?>

<?php
$this->breadcrumbs=array(
	Yii::t('app','model.Formulario')=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>Yii::t('app','model.Formulario.index'),'url'=>array('index')),
	array('label'=>Yii::t('app','model.Formulario.create'),'url'=>array('create')),
	array('label'=>Yii::t('app','model.Formulario.update'),'url'=>array('update','id'=>$model->id)),
	array('label'=>Yii::t('app','model.Formulario.delete'),'url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app','model.Formulario.admin'),'url'=>array('admin')),
);
?>

<h1><?php echo Yii::t('app','model.Formulario.view');?></h1>

<?php $this->widget('zii.widgets.CDetailView',array(
    'htmlOptions' => array(
        'class' => 'table table-striped table-condensed table-hover',
    ),
    'data'=>$model,
    'attributes'=>array(
		'id',
		'notas',
		'visita_id',
	),
)); ?>