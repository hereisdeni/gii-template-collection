<?php echo "<?php\n"; ?>

class <?php echo $this->controllerClass; ?> extends <?php echo $this->baseControllerClass."\n"; ?>
{
	public $layout='//layouts/column2';

	public function filters()
	{
		return array(
			'accessControl', 
		);
	}	

	public function accessRules()
	{
		return array(
			array('allow',  
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', 
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', 
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  
				'users'=>array('*'),
			),
		);
	}

	public function actionView($<?php echo $this->identificationColumn; ?>)
	{
		$model = $this->loadModel($<?php echo $this->identificationColumn; ?>);
		$this->render('view',array(
			'model' => $model,
		));
	}

	public function actionCreate()
	{
		$model = new <?php echo $this->modelClass; ?>;

		<?php if($this->validation == 1 || $this->validation == 3) { ?>
		$this->performAjaxValidation($model, '<?php echo $this->class2id($this->modelClass)?>-form');
    <?php } ?>

		if(isset($_POST['<?php echo $this->modelClass; ?>'])) {
			$model->attributes = $_POST['<?php echo $this->modelClass; ?>'];

<?php
			// Add additional MANY_MANY Attributes to the model object
			foreach(CActiveRecord::model($this->modelClass)->relations() as $key => $relation)
			{
				if($relation[0] == 'CManyManyRelation')
				{
					printf("\t\t\tif(isset(\$_POST['%s']['%s']))\n", $this->modelClass, $relation[1]);
					printf("\t\t\t\t\$model->setRelationRecords('%s', \$_POST['%s']['%s']);\n", $key, $this->modelClass, $relation[1]);
				}
			}
?>
			try {
    			if($model->save()) {
        			$this->redirect(array('view','<?php echo $this->identificationColumn;?>'=>$model-><?php echo $this->identificationColumn; ?>));
				}
			} catch (Exception $e) {
				$model->addError('<?php echo $this->identificationColumn;?>', $e->getMessage());
			}
		} elseif(isset($_GET['<?php echo $this->modelClass; ?>'])) {
				$model->attributes = $_GET['<?php echo $this->modelClass; ?>'];
		}

		$this->render('create',array( 'model'=>$model));
	}


	public function actionUpdate($<?php echo $this->identificationColumn; ?>)
	{
		$model = $this->loadModel($<?php echo $this->identificationColumn; ?>);

		<?php if($this->validation == 1 || $this->validation == 3) { ?>
		$this->performAjaxValidation($model, '<?php echo $this->class2id($this->modelClass)?>-form');
		<?php } ?>

		if(isset($_POST['<?php echo $this->modelClass; ?>']))
		{
			$model->attributes = $_POST['<?php echo $this->modelClass; ?>'];

<?php
		foreach(CActiveRecord::model($this->modelClass)->relations() as $key => $relation)
			{
				if($relation[0] == 'CManyManyRelation')
				{
					printf("\t\t\tif(isset(\$_POST['%s']['%s']))\n", $this->modelClass, $relation[1]);
					printf("\t\t\t\t\$model->setRelationRecords('%s', \$_POST['%s']['%s']);\n", $key, $this->modelClass, $relation[1]);
					echo "else\n";
					echo "\$model->setRelationRecords('{$key}',array());\n";
				}
			}
?>

			try {
    			if($model->save()) {
        			$this->redirect(array('view','<?php echo $this->identificationColumn;?>'=>$model-><?php echo $this->identificationColumn; ?>));
        		}
			} catch (Exception $e) {
				$model->addError('<?php echo $this->identificationColumn;?>', $e->getMessage());
			}	
		}

		$this->render('update',array(
					'model'=>$model,
					));
	}

	public function actionDelete($<?php echo $this->identificationColumn; ?>)
	{
		if(Yii::app()->request->isPostRequest)
		{
			try {
				$this->loadModel($<?php echo $this->identificationColumn; ?>)->delete();
			} catch (Exception $e) {
				throw new CHttpException(500,$e->getMessage());
			}

			if(!isset($_GET['ajax']))
			{
					$this->redirect(array('admin'));
			}
		}
		else
			throw new CHttpException(400,
					Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
	}

	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('<?php echo $this->modelClass; ?>');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	public function actionAdmin()
	{
		$model=new <?php echo $this->modelClass; ?>('search');
		$model->unsetAttributes();

		if(isset($_GET['<?php echo $this->modelClass; ?>']))
			$model->attributes = $_GET['<?php echo $this->modelClass; ?>'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	public function loadModel($<?php echo $this->identificationColumn; ?>)
	{
		// TODO: is_numeric is for backward compatibility ... if the value is a number it's treated as the PK
		// Protest ! :) - the 'title' can containt only numbers, even if not the PK
		// is meant ! We need to think about another 'fallback' technique! - thyseus
			$model=<?php echo $this->modelClass; ?>::model()->find('<?php echo $this->identificationColumn; ?> = :<?php echo $this->identificationColumn; ?>', array(
			':<?php echo $this->identificationColumn; ?>' => $<?php echo $this->identificationColumn; ?>));
		if($model===null)
			throw new CHttpException(404,Yii::t('app', 'The requested page does not exist.'));
		return $model;
	}

	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='<?php echo $this->class2id($this->modelClass); ?>-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
