<?php
return [
  'disk'=>env('EVIDENCE_DISK', env('FILESYSTEM_DISK','local')),
  'clamav'=>['enabled'=>env('CLAMAV_ENABLED', false),'host'=>env('CLAMAV_HOST','127.0.0.1'),'port'=>env('CLAMAV_PORT',3310)],
  'immutable'=>true,
];