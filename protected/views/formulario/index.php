<?php
/* @var $this FormularioController */
/* @var $dataProvider CActiveDataProvider */
?>

<?php
$this->breadcrumbs=array(
	Yii::t('app','model.Formulario')
,
);

$this->menu=array(
	array('label'=>Yii::t('app','model.Formulario.create'),'url'=>array('create')),
	array('label'=>Yii::t('app','model.Formulario.admin'),'url'=>array('admin')),
);
?>

<h1><?php echo Yii::t('app','model.Formulario')
;?></h1>

<?php $this->widget('bootstrap.widgets.TbListView',array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>