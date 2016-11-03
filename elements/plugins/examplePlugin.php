<?php
if($modx->event->name != 'OnHandleRequest') return;

$modx->log(MODX_LOG_LEVEL_INFO, "It's worked! Run examplePlugin!");