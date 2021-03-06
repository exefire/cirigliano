<?php

class FacturacionController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column1';

	/**
	 * @return array action filters
	 */
	public function filters() {
     return array( 
        //it's important to add site/error, so an unpermitted user will get the error.
        array('auth.filters.AuthFilter'),
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
				'actions'=>array(),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','update'),
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
		$model=new Canal;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['Canal'])) {
			$model->attributes=$_POST['Canal'];
			if ($model->save()) {
				$this->redirect(array('admin'));
			}
		}

		$this->render('create',array(
			'model'=>$model,
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

		if (isset($_POST['Canal'])) {
			$model->attributes=$_POST['Canal'];
			if ($model->save()) {
				$this->redirect(array('admin'));
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
		$option = 1;
		if(date('d')>=25){
			$mes = date('m-Y',strtotime('+1 month'));
		}
		else
			$mes = date('m-Y');
		$criteria = new CDbCriteria();
		$criteriaExcelencia = new CDbCriteria();
		if(isset($_POST['adicional']) && $_POST['adicional']['descripcion'] != '' && $_POST['adicional']['monto'] != ''){
			$a = new AdicionalFacturacion;
			$a->periodo = $_POST['adicional']['periodo'];
			$a->descripcion = $_POST['adicional']['descripcion'];
			$a->monto = $_POST['adicional']['monto'];
			$a->save();
			$option = 3;
		}

		if(isset($_POST['filtros']) && $_POST['filtros']['mes'] != ''){
			$mes = $_POST['filtros']['mes'];
		}
		$adicionales = AdicionalFacturacion::model()->findAll(array('condition'=>'periodo ="'.$mes.'"'));
		$adicionalesDataProvider = new CActiveDataProvider('AdicionalFacturacion',array('criteria'=>array(
			'condition'=>'periodo ="'.$mes.'"',
			)));
		$foo = explode('-', $mes);
		$bar = array('-');
		if($foo[0] > 1){
			$bar[0] = $foo[0]-1;
			$bar[1] = $foo[1];
		}
		if($foo[0] == 1){
			$bar[0] = 12;
			$bar[1] = $foo[1]-1;
		}

		$desde = $bar[1].'-'.str_pad($bar[0],2,0,STR_PAD_LEFT).'-25';
		$hasta = $foo[1].'-'.str_pad($foo[0],2,0,STR_PAD_LEFT).'-24';
		
		$criteria->addCondition('visita.tipo_visita_id != 4');
		$criteriaExcelencia->addCondition('visita.tipo_visita_id = 4');

		$criteria->addBetweenCondition('visita.fecha_creacion',$desde,$hasta);
		$criteriaExcelencia->addBetweenCondition('visita.fecha_creacion',$desde,$hasta);


		$presupuestos = Presupuesto::model()->with('visita')->findAll($criteria);
		$presupuestosExcelencia = Presupuesto::model()->with('visita')->findAll($criteriaExcelencia);
		
		$this->render('index',array(
			'presupuestos'=>$presupuestos,
			'presupuestosExcelencia'=>$presupuestosExcelencia,
			'mes'=>$mes,
			'adicionales'=>$adicionales,
			'adicionalesDataProvider'=>$adicionalesDataProvider,
			'option'=>$option,
			'desde'=>$desde,
			'hasta'=>$hasta,
		));
	}

	public function actionDeleteAdicional($id){
		if (Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$adicional = AdicionalFacturacion::model()->findByPk($id);
			$adicional->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if (!isset($_GET['ajax'])) {
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
			}
		} else {
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
		}
	}

	public function actionExportarExcel($mes){
		$criteria = new CDbCriteria();
		$criteriaExcelencia = new CDbCriteria();
		$adicionales = AdicionalFacturacion::model()->findAll(array('condition'=>'periodo ="'.$mes.'"'));

		$foo = explode('-', $mes);
		$bar = array('-');
		if($foo[0] > 1){
			$bar[0] = $foo[0]-1;
			$bar[1] = $foo[1];
		}
		if($foo[0] == 1){
			$bar[0] = 12;
			$bar[1] = $foo[1]-1;
		}

		$desde = $bar[1].'-'.str_pad($bar[0],2,0,STR_PAD_LEFT).'-25';
		$hasta = $foo[1].'-'.str_pad($foo[0],2,0,STR_PAD_LEFT).'-24';
		
		$criteria->addCondition('visita.tipo_visita_id != 4');
		$criteriaExcelencia->addCondition('visita.tipo_visita_id = 4');

		$criteria->addBetweenCondition('visita.fecha_creacion',$desde,$hasta);
		$criteriaExcelencia->addBetweenCondition('visita.fecha_creacion',$desde,$hasta);

		$presupuestos = Presupuesto::model()->with('visita')->findAll($criteria);
		$presupuestosExcelencia = Presupuesto::model()->with('visita')->findAll($criteriaExcelencia);
		//print_r($presupuestos);
		//echo "<br>";
		//print_r($presupuestosExcelencia);
		
		$totalAdicionales = 0;
		foreach ($adicionales as $adicional) {
			if($adicional)
				$totalAdicionales +=$adicional->monto;
		}
		$subtotales = array('reparaciones'=>array('total'=>0,'espPresup'=>0,'finalizados'=>0),'traslados'=>array('total'=>0,'espPresup'=>0,'finalizados'=>0));
		foreach ($presupuestos as $presupuesto) {
			if($presupuesto->visita->tipo_visita_id == 3){
				$subtotales['traslados']['total'] += $presupuesto->total;
				if($presupuesto->visita->estado == 1){
					$subtotales['traslados']['espPresup'] += $presupuesto->total;
				}
				if($presupuesto->visita->estado == 4){
					$subtotales['traslados']['finalizados'] += $presupuesto->total;
				}
			}
			if($presupuesto->visita->tipo_visita_id != 3){
				$subtotales['reparaciones']['total'] += $presupuesto->total;
				if($presupuesto->visita->estado == 1){
					$subtotales['reparaciones']['espPresup'] += $presupuesto->total;
				}
				if($presupuesto->visita->estado == 4){
					$subtotales['reparaciones']['finalizados'] += $presupuesto->total;
				}
			}
		}
		Yii::import('application.extensions.PHPExcel',true);


		$objPHPExcel = new PHPExcel();
	    $styleArray = array(
	    'font'  => array(
	        'bold'  => true,
	        'size'  => 11,
	        'name'  => 'Verdana'
	    ));
	    //RESUMEN REPARACIONES
		$objPHPExcel->setActiveSheetIndex(0)
		    ->setCellValue('A1', 'Item')
		    ->setCellValue('B1', 'Espera Aprobación Presupuesto')
		    ->setCellValue('C1', 'Finalizados')
		    ->setCellValue('D1', 'Total');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);	
	
	    $row =2;
	    $objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'Reparaciones')
					->setCellValueByColumnAndRow(1, $row, $subtotales['reparaciones']['espPresup'])
					->setCellValueByColumnAndRow(2, $row, $subtotales['reparaciones']['finalizados'])
					->setCellValueByColumnAndRow(3, $row, $subtotales['reparaciones']['total']);

		$row =3;
	    $objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'Traslados')
					->setCellValueByColumnAndRow(1, $row, $subtotales['traslados']['espPresup'])
					->setCellValueByColumnAndRow(2, $row, $subtotales['traslados']['finalizados'])
					->setCellValueByColumnAndRow(3, $row, $subtotales['traslados']['total']);

		$row =4;
	    $objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'Adicionales')
					->setCellValueByColumnAndRow(3, $row, $totalAdicionales);

		$row =5;
	    $objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'Total')
					->setCellValueByColumnAndRow(1, $row, $subtotales['reparaciones']['espPresup']+$subtotales['traslados']['espPresup'])
					->setCellValueByColumnAndRow(2, $row, $subtotales['reparaciones']['finalizados']+$subtotales['traslados']['finalizados'])
					->setCellValueByColumnAndRow(3, $row, $subtotales['reparaciones']['total']+$subtotales['traslados']['total']+$totalAdicionales);
		$objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($styleArray);	

		$row = 8;
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'Neto:')
					->setCellValueByColumnAndRow(1, $row, $subtotales['reparaciones']['total']+$subtotales['traslados']['total']+$totalAdicionales);
		$objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray($styleArray);	

		$row = 9;
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'IVA:')
					->setCellValueByColumnAndRow(1, $row, round(0.19*($subtotales['reparaciones']['total']+$subtotales['traslados']['total']+$totalAdicionales),0));
		$objPHPExcel->getActiveSheet()->getStyle('A9')->applyFromArray($styleArray);	

		$row = 10;
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'Total:')
					->setCellValueByColumnAndRow(1, $row, round(1.19*($subtotales['reparaciones']['total']+$subtotales['traslados']['total']+$totalAdicionales),0));
		$objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray($styleArray);	

		$row = 12;
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'Presupuesto Total:')
					->setCellValueByColumnAndRow(1, $row, 20000000);
		$objPHPExcel->getActiveSheet()->getStyle('A12')->applyFromArray($styleArray);	
		
		$row = 13;
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'Neto:')
					->setCellValueByColumnAndRow(1, $row, $subtotales['reparaciones']['total']+$subtotales['traslados']['total']+$totalAdicionales);
		$objPHPExcel->getActiveSheet()->getStyle('A13')->applyFromArray($styleArray);	
		
		$row = 14;
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $row, 'Presupuesto Disponible:')
					->setCellValueByColumnAndRow(1, $row, 20000000-($subtotales['reparaciones']['total']+$subtotales['traslados']['total']+$totalAdicionales));
		$objPHPExcel->getActiveSheet()->getStyle('A14')->applyFromArray($styleArray);

		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('A')
		    ->setAutoSize(true);
	    $objPHPExcel->getActiveSheet()
		    ->getColumnDimension('B')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('C')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setTitle('Resumen Reparaciones');

		//REPARACIONES
		$reparaciones = $objPHPExcel->createSheet();
		$reparaciones->setTitle('Reparaciones');
		$objPHPExcel->setActiveSheetIndex(1);
		$reparaciones
		    ->setCellValue('A1', 'Folio')
		    ->setCellValue('B1', 'Punto')
		    ->setCellValue('C1', 'Fecha')
		    ->setCellValue('D1', 'Mueble')
		    ->setCellValue('E1', 'Elemento')
		    ->setCellValue('F1', 'Cantidad')
		    ->setCellValue('G1', 'Valor Unitario')
		    ->setCellValue('H1', 'Total');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($styleArray);	
		
		$row =2;
		foreach ($presupuestos as $presupuesto) {
			$datosMueblePunto = array();
            foreach ($presupuesto->mueblespresupuesto as $accion) {
                if(isset($datosMueblePunto[$accion->mueble_punto_id]))
                    $datosMueblePunto[$accion->mueble_punto_id]['accion'][]=$accion;
                else
                    $datosMueblePunto[$accion->mueble_punto_id]=array('accion'=>array($accion),'manobra'=>array(),'adicional'=>array());
            }
            foreach ($presupuesto->manosobra as $a) {
                if(isset($datosMueblePunto[$a->mueble_punto_id]))
                    $datosMueblePunto[$a->mueble_punto_id]['manobra'][]=$a;
                else
                    $datosMueblePunto[$a->mueble_punto_id]=array('accion'=>array(),'manobra'=>array($a),'adicional'=>array());
            }
            foreach ($presupuesto->adicionales as $adicional) {
                if(isset($datosMueblePunto[$adicional->mueble_punto_id]))
                    $datosMueblePunto[$adicional->mueble_punto_id]['adicional'][]=$adicional;
                else
                    $datosMueblePunto[$adicional->mueble_punto_id]=array('accion'=>array(),'manobra'=>array(),'adicional'=>array($adicional));
            }

			if ($presupuesto->tarifa_visita_preventiva != null && $presupuesto->tarifa_visita_preventiva != 0) {
	    		$reparaciones
					->setCellValueByColumnAndRow(0, $row, $presupuesto->visita->folio)
					->setCellValueByColumnAndRow(1, $row, $presupuesto->visita->punto->DireccionDescripcion)
					->setCellValueByColumnAndRow(2, $row, date('d-m-Y',strtotime($presupuesto->visita->fecha_visita)))
					->setCellValueByColumnAndRow(4, $row, 'Visita Preventiva')
					->setCellValueByColumnAndRow(5, $row, 1)
					->setCellValueByColumnAndRow(6, $row, $presupuesto->tarifa_visita_preventiva)
					->setCellValueByColumnAndRow(7, $row, $presupuesto->tarifa_visita_preventiva);

				$row++;
			}
			foreach ($datosMueblePunto as $value) {
				foreach ($value['accion'] as $accion) {
					$reparaciones
					->setCellValueByColumnAndRow(0, $row, $presupuesto->visita->folio)
					->setCellValueByColumnAndRow(1, $row, $presupuesto->visita->punto->DireccionDescripcion)
					->setCellValueByColumnAndRow(2, $row, date('d-m-Y',strtotime($presupuesto->visita->fecha_visita)))
					->setCellValueByColumnAndRow(3, $row, $accion->mueblepunto->Descripcion)
					->setCellValueByColumnAndRow(4, $row, strip_tags($accion->servicio->descripcion))
					->setCellValueByColumnAndRow(5, $row, $accion->cant_servicio)
					->setCellValueByColumnAndRow(6, $row, $accion->tarifa_servicio)
					->setCellValueByColumnAndRow(7, $row, $accion->cant_servicio*$accion->tarifa_servicio);

					$row++;
				}
				foreach ($value['adicional'] as $adicional) {
					$reparaciones
					->setCellValueByColumnAndRow(0, $row, $presupuesto->visita->folio)
					->setCellValueByColumnAndRow(1, $row, $presupuesto->visita->punto->DireccionDescripcion)
					->setCellValueByColumnAndRow(2, $row, date('d-m-Y',strtotime($presupuesto->visita->fecha_visita)))
					->setCellValueByColumnAndRow(3, $row, $adicional->mueblePunto->Descripcion)
					->setCellValueByColumnAndRow(4, $row, strip_tags($adicional->Descripcion))
					->setCellValueByColumnAndRow(5, $row, $adicional->cantidad)
					->setCellValueByColumnAndRow(6, $row, $adicional->tarifa)
					->setCellValueByColumnAndRow(7, $row, $adicional->cantidad*$adicional->tarifa);

					$row++;
				}
				foreach ($value['manobra'] as $m) {
					$reparaciones
					->setCellValueByColumnAndRow(0, $row, $presupuesto->visita->folio)
					->setCellValueByColumnAndRow(1, $row, $presupuesto->visita->punto->DireccionDescripcion)
					->setCellValueByColumnAndRow(2, $row, date('d-m-Y',strtotime($presupuesto->visita->fecha_visita)))
					->setCellValueByColumnAndRow(3, $row, $m->mueblepunto->Descripcion)
					->setCellValueByColumnAndRow(4, $row, strip_tags($m->Descripcion))
					->setCellValueByColumnAndRow(5, $row, 1)
					->setCellValueByColumnAndRow(6, $row, $m->Tarifa)
					->setCellValueByColumnAndRow(7, $row, $m->Tarifa);

					$row++;
				}
			}
		}
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('A')
		    ->setAutoSize(true);
	    $objPHPExcel->getActiveSheet()
		    ->getColumnDimension('B')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('C')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('D')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('E')
		    ->setAutoSize(true);
	    $objPHPExcel->getActiveSheet()
		    ->getColumnDimension('F')
		    ->setAutoSize(true);
	    $objPHPExcel->getActiveSheet()
		    ->getColumnDimension('G')
		    ->setAutoSize(true);
     
		//TRASLADOS
		$medios = array('1'=>'Camioneta, 3,5Mts3','2'=>'Camión ¾, 6,5Mts3','3'=>'Camión, 30Mts3','4'=>'Carro ffvv terreno');
		$traslados = $objPHPExcel->createSheet();
		$traslados->setTitle('Traslados');
		$objPHPExcel->setActiveSheetIndex(2);
		$traslados
		    ->setCellValue('A1', 'Folio')
		    ->setCellValue('B1', 'Punto Origen')
		    ->setCellValue('C1', 'Punto Destino')
		    ->setCellValue('D1', 'Fecha')
		    ->setCellValue('E1', 'Detalle')
		    ->setCellValue('F1', 'Medio')
		    ->setCellValue('G1', 'Total');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($styleArray);	
			
		$row =2;

		foreach ($presupuestos as $presupuesto) {
			if($presupuesto->visita->tipo_visita_id == 3){
				foreach ($presupuesto->tarifasTraslado as $tm) {
					# code...
					$traslados
						->setCellValueByColumnAndRow(0, $row, $presupuesto->visita->folio)
						->setCellValueByColumnAndRow(1, $row, $presupuesto->visita->punto->DireccionDescripcion)
						->setCellValueByColumnAndRow(2, $row, $presupuesto->visita->destino->DireccionDescripcion)
						->setCellValueByColumnAndRow(3, $row, date('d-m-Y',strtotime($presupuesto->visita->fecha_visita)))
						->setCellValueByColumnAndRow(4, $row, $tm->tarifaTraslado->Descripcion)
						->setCellValueByColumnAndRow(5, $row, $medios[$tm->tipo_tarifa_traslado])
						->setCellValueByColumnAndRow(6, $row, $tm->TTraslado);

					$row++;
				}
				foreach ($presupuesto->trasladopresupuesto as $traslado) {
					if ($traslado->tarifa_instalacion != null && $traslado->tarifa_instalacion != 0){
						$traslados
							->setCellValueByColumnAndRow(0, $row, $presupuesto->visita->folio)
							->setCellValueByColumnAndRow(1, $row, $presupuesto->visita->punto->DireccionDescripcion)
							->setCellValueByColumnAndRow(2, $row, $presupuesto->visita->destino->DireccionDescripcion)
							->setCellValueByColumnAndRow(3, $row, date('d-m-Y',strtotime($presupuesto->visita->fecha_visita)))
							->setCellValueByColumnAndRow(4, $row, 'Instalación '.$traslado->mueblePunto->Descripcion)
							->setCellValueByColumnAndRow(6, $row, $traslado->tarifa_instalacion);
							
						$row++;

					}
					if ($traslado->tarifa_desinstalacion != null && $traslado->tarifa_desinstalacion != 0){
						$traslados
							->setCellValueByColumnAndRow(0, $row, $presupuesto->visita->folio)
							->setCellValueByColumnAndRow(1, $row, $presupuesto->visita->punto->DireccionDescripcion)
							->setCellValueByColumnAndRow(2, $row, $presupuesto->visita->destino->DireccionDescripcion)
							->setCellValueByColumnAndRow(3, $row, date('d-m-Y',strtotime($presupuesto->visita->fecha_visita)))
							->setCellValueByColumnAndRow(4, $row, 'Desinstalación '.$traslado->mueblePunto->Descripcion)
							->setCellValueByColumnAndRow(6, $row, $traslado->tarifa_desinstalacion);
							
						$row++;
					}

				}
			}
		}
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('A')
		    ->setAutoSize(true);
	    $objPHPExcel->getActiveSheet()
		    ->getColumnDimension('B')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('C')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('D')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('E')
		    ->setAutoSize(true);
	    $objPHPExcel->getActiveSheet()
		    ->getColumnDimension('F')
		    ->setAutoSize(true);

		//Adicionales
		$ad = $objPHPExcel->createSheet();
		$ad->setTitle('Adicionales');
		$objPHPExcel->setActiveSheetIndex(3);
		$ad
		    ->setCellValue('A1', 'Descripcion')
		    ->setCellValue('B1', 'Monto');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);	
			
		$row =2;
		$netoAdicionales = 0;
		foreach ($adicionales as $adicional) {
			$ad
			->setCellValueByColumnAndRow(0, $row, $adicional->descripcion)
			->setCellValueByColumnAndRow(1, $row, $adicional->monto);
			$netoAdicionales +=$adicional->monto;
			$row++;	
		}

		$row +=2;
		$ad
					->setCellValueByColumnAndRow(0, $row, 'Neto:')
					->setCellValueByColumnAndRow(1, $row, $netoAdicionales);
		$objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($styleArray);	

		$row++;
		$ad
					->setCellValueByColumnAndRow(0, $row, 'IVA:')
					->setCellValueByColumnAndRow(1, $row, round(0.19*($netoAdicionales),0));
		$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($styleArray);	

		$row++;
		$ad
					->setCellValueByColumnAndRow(0, $row, 'Total:')
					->setCellValueByColumnAndRow(1, $row, round(1.19*($netoAdicionales),0));
		$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($styleArray);	

		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('A')
		    ->setAutoSize(true);
	    $objPHPExcel->getActiveSheet()
		    ->getColumnDimension('B')
		    ->setAutoSize(true);
		


		//RESUMEN RUTA EXCELENCIA
		$subtotalesExcelencia = array('reparaciones'=>array('total'=>0,'espPresup'=>0,'finalizados'=>0));
		foreach ($presupuestosExcelencia as $presupuesto) {	
			if($presupuesto->visita->tipo_visita_id != 3){
				$subtotalesExcelencia['reparaciones']['total'] += $presupuesto->total;
				if($presupuesto->visita->estado == 1){
					$subtotalesExcelencia['reparaciones']['espPresup'] += $presupuesto->total;
				}
				if($presupuesto->visita->estado == 4){
					$subtotalesExcelencia['reparaciones']['finalizados'] += $presupuesto->total;
				}
			}
		}
		$rutaExcelencia = $objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(4);
		$rutaExcelencia
		    ->setCellValue('A1', 'Item')
		    ->setCellValue('B1', 'Espera Aprobación Presupuesto')
		    ->setCellValue('C1', 'Finalizados')
		    ->setCellValue('D1', 'Total');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);	
		$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);	
	
	    $row =2;
	    $rutaExcelencia
					->setCellValueByColumnAndRow(0, $row, 'Reparaciones')
					->setCellValueByColumnAndRow(1, $row, $subtotalesExcelencia['reparaciones']['espPresup'])
					->setCellValueByColumnAndRow(2, $row, $subtotalesExcelencia['reparaciones']['finalizados'])
					->setCellValueByColumnAndRow(3, $row, $subtotalesExcelencia['reparaciones']['total']);
		$row =3;
	    $rutaExcelencia
					->setCellValueByColumnAndRow(0, $row, 'Total')
					->setCellValueByColumnAndRow(1, $row, $subtotalesExcelencia['reparaciones']['espPresup'])
					->setCellValueByColumnAndRow(2, $row, $subtotalesExcelencia['reparaciones']['finalizados'])
					->setCellValueByColumnAndRow(3, $row, $subtotalesExcelencia['reparaciones']['total']);
		$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);	

		$row = 5;
		$rutaExcelencia
					->setCellValueByColumnAndRow(0, $row, 'Neto:')
					->setCellValueByColumnAndRow(1, $row, $subtotalesExcelencia['reparaciones']['total']);
		$objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($styleArray);	

		$row = 6;
		$rutaExcelencia
					->setCellValueByColumnAndRow(0, $row, 'IVA:')
					->setCellValueByColumnAndRow(1, $row, round(0.19*($subtotalesExcelencia['reparaciones']['total']),0));
		$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($styleArray);	

		$row = 7;
		$rutaExcelencia
					->setCellValueByColumnAndRow(0, $row, 'Total:')
					->setCellValueByColumnAndRow(1, $row, round(1.19*($subtotalesExcelencia['reparaciones']['total']),0));
		$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($styleArray);	

		$row = 10;
		$rutaExcelencia
					->setCellValueByColumnAndRow(0, $row, 'Presupuesto Total:')
					->setCellValueByColumnAndRow(1, $row, 20000000);
		$objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray($styleArray);	
		
		$row = 11;
		$rutaExcelencia
					->setCellValueByColumnAndRow(0, $row, 'Neto:')
					->setCellValueByColumnAndRow(1, $row, $subtotalesExcelencia['reparaciones']['total']);
		$objPHPExcel->getActiveSheet()->getStyle('A11')->applyFromArray($styleArray);	
		
		$row = 12;
		$rutaExcelencia
					->setCellValueByColumnAndRow(0, $row, 'Presupuesto Disponible:')
					->setCellValueByColumnAndRow(1, $row, 20000000-($subtotalesExcelencia['reparaciones']['total']));
		$objPHPExcel->getActiveSheet()->getStyle('A12')->applyFromArray($styleArray);

		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('A')
		    ->setAutoSize(true);
	    $objPHPExcel->getActiveSheet()
		    ->getColumnDimension('B')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()
		    ->getColumnDimension('C')
		    ->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setTitle('Resumen Ruta Excelencia');

       	$objPHPExcel->setActiveSheetIndex(0);
 

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-Disposition: attachment;filename="Facturacion '.$mes.'.xlsx"');
	    header('Cache-Control: max-age=0');
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save('php://output');
	    exit;
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Canal('search');
		$model->unsetAttributes();  // clear any default values
		if (isset($_GET['Canal'])) {
			$model->attributes=$_GET['Canal'];
		}

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Canal the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Canal::model()->findByPk($id);
		if ($model===null) {
			throw new CHttpException(404,'The requested page does not exist.');
		}
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Canal $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if (isset($_POST['ajax']) && $_POST['ajax']==='canal-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}