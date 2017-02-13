<?php
namespace KazSudo\Google\Language;

use Google\Cloud\ServiceBuilder;

class ServiceWrapper
{
  protected $config;
  protected $gcloud;
  public function __construct($config_file_path)
  {
    $this->loadConfig($config_file_path);
    $this->gcloud = new ServiceBuilder(['keyFilePath' => $this->config['google_cloud']['key_file_path']]);
  }

  protected function loadConfig($config_file_path)
  {
    $this->config = include $config_file_path;
  }

  public function analyzeText($content, $name){
    $info = $this->annotateText($content);
    if($info){
      $info['_name'] = $name;
      $ret = $this->saveToDataStore($info);
      if($ret){
        return ['_id' => $ret, '_name' => $info['_name']];
      }
    }
    return null;
  }

  public function annotateText($content)
  {
    $options = ['features' => ['entities', 'syntax', 'sentiment']];
    $language = $this->gcloud->naturalLanguage();
    $annotation = $language->annotateText($content, $options);
    return $annotation->info();
  }

  public function annotateTextFromFile($file)
  {
    $content = file_get_contents($file);
    $info = $this->annotateText($content);
    if($info){
      $info['_file'] = $info['_name'] = basename($file);
    }
    return $info;
  }

  public function annotateTextFromCloudStorage($file)
  {
    if(preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $file, $matchs)){
      $bucketName = $matchs[1];
      $objectName = $matchs[2];
    }
    else {
      return null;
    }
    $storage = $this->gcloud->storage();
    $bucket = $storage->bucket($bucketName);
    $storageObject = $bucket->object($objectName);
    $info = $this->annotateText($storageObject);
    if($info){
      $info['_file'] = $file;
      $info['_name'] = basename($objectName);
    }
    return $info;
  }

  public function getConfig($category, $name){
    return $this->config[$category][$name];
  }

  public function saveToDataStore($info)
  {
    $kind = $this->config['google_cloud_datastore']['kind'];
    $name = $info['_name'];
    $datastore = $this->gcloud->datastore();
    $key = $datastore->key($kind, $name);
    $entity = $datastore->entity($key, $info);
    return $datastore->upsert($entity);
  }

  public function getEntityFromDataStore($name)
  {
    $kind = $this->config['google_cloud_datastore']['kind'];
    $datastore = $this->gcloud->datastore();
    $key = $datastore->key($kind, $name);
    $entity = null;
    if($name){
      $key = $datastore->key($kind, $name);
      $entity = $datastore->lookup($key);
    }
    return $entity;
  }
}
