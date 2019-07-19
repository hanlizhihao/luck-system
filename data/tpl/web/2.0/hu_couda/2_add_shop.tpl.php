<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('header', TEMPLATE_INCLUDEPATH)) : (include template('header', TEMPLATE_INCLUDEPATH));?>

<div class="main">
    <div class="panel panel-info">
        <div class="panel-heading">添加店铺</div>
        <div class="panel-body">
            <form action="<?php  echo $this->createWeburl('shop');?>" method="post" class="form-horizontal" id="myform">

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">店铺名称</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="shop_name" placeholder="店铺名称" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="" >
                        <input type="hidden" name="op" value="add">
                        <input type="submit" name="submit" id="submit" value="提交" class="btn btn-primary">
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>



