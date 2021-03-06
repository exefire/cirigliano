<?php

class NotificarPersonaController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{

		$model=new NotificarPersona;
		$persona = new Persona;
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['Tipo']) && isset($_POST['NotificarPersona']) && !empty($_POST['NotificarPersona']['persona_id']) ) {
			$cant = 0;
			$flag = 0;
			foreach ($_POST['Tipo'] as $key => $value) {
				$cant = count($_POST['Tipo']);
				$model=new NotificarPersona;
				$model->attributes=$_POST['NotificarPersona'];
				if(!NotificarPersona::model()->exists('persona_id ='.$model->persona_id.' AND tipo_notificacion ='.$key)){
					$model->tipo_notificacion = $key;
					if(isset($_POST['opcion']['canal']) && $_POST['opcion']['canal']!=''){
						$model->global = 0;
						$model->canal_id = $_POST['opcion']['canal'];
					}
					else{
						$model->global = 1;
					}
					if($model->save())
						$flag++;
				}
				else
					$flag++;
			}
			if($cant > 0 && $flag == $cant)
				$this->redirect(array('admin'));
		}
		if (isset($_POST['Tipo']) && isset($_POST['Persona']) && !empty($_POST['Persona']['nombre']) && !empty($_POST['Persona']['email']) ) {
			$cant = 0;
			$flag = 0;
			$persona->attributes=$_POST['Persona'];
			$persona->save();
			foreach ($_POST['Tipo'] as $key => $value) {
				$cant = count($_POST['Tipo']);
				$model=new NotificarPersona;
				$model->persona_id = $persona->id;
				$model->tipo_notificacion = $key;
				if(isset($_POST['opcion']['canal']) && $_POST['opcion']['canal']!=''){
						$model->global = 0;
						$model->canal_id = $_POST['opcion']['canal'];
				}
				else{
					$model->global = 1;
				}
				if($model->save())
					$flag++;
			}
			if($cant > 0 && $flag == $cant)
				$this->redirect(array('admin'));
		}
		if ( (isset($_POST['NotificarPersona']) || isset($_POST['Persona'])) && !isset($_POST['Tipo']) ) {
			$model->addError('tipo_notificacion','Debe seleccionar al menos 1 tipo de notificación');
		}


		$this->render('create',array(
			'model'=>$model,
			'persona'=>$persona,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['NotificarPersona'])) {
			$model->attributes=$_POST['NotificarPersona'];
			if ($model->save()) {
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if (Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if (!isset($_GET['ajax'])) {
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
			}
		} else {
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
		}
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$this->actionAdmin();
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new NotificarPersona('search');
		$model->unsetAttributes();  // clear any default values
		if (isset($_GET['NotificarPersona'])) {
			$model->attributes=$_GET['NotificarPersona'];
		}

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return NotificarPersona the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=NotificarPersona::model()->findByPk($id);
		if ($model===null) {
			throw new CHttpException(404,'The requested page does not exist.');
		}
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param NotificarPersona $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if (isset($_POST['ajax']) && $_POST['ajax']==='notificar-persona-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}