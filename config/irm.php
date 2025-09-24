<?php
return [
  'score_formula' => env('RISK_SCORE_FORMULA', 'impact * likelihood * weight'),
  'impact_levels' => [1,2,3,4,5],
  'likelihood_levels' => [1,2,3,4,5],
  'bands' => [
    ['min'=>1,  'max'=>5,  'label'=>'Low',     'color'=>'#9bd67d'],
    ['min'=>6,  'max'=>12, 'label'=>'Medium',  'color'=>'#ffd166'],
    ['min'=>13, 'max'=>20, 'label'=>'High',    'color'=>'#f4a261'],
    ['min'=>21, 'max'=>100,'label'=>'Extreme', 'color'=>'#e76f51'],
  ],
];
