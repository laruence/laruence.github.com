<?php
/**
 * A 24 maker
 * @version 1.0.0
 * @author laruence<laruence at yahoo.com.cn>
 * @copyright (c) 2009 http://www.laruence.com
 */
 
class TwentyFourCal extends GtkWindow {
    private $chalkboard;
    private $inputs;
    public  $needle = 24;
    public  $precision = '1e-6';
 
    function TwentyFourCal() {
        parent::__construct();
        $this->draw();
        $this->show();
    }
    
    /**
     * 画窗体方法
     */
    public function draw() {
        $this->set_default_size(200, 200); 
        $this->set_title("24计算器"); 
 
        $mesg   = new GtkLabel('Please input 4 integer(0-99):');
        $this->chalkboard = new GtkLabel();
 
        $this->inputs = $inputs = array(
            new GtkEntry(),
            new GtkEntry(),
            new GtkEntry(),
            new GtkEntry()
        );
 
        /**
         * container
         */
        $table = new GtkTable(4, 1, 0);
        $layout = array(
            'left'  => 0, 
            'right' => 1, 
            'top'    => 0, 
            'bottom' => 1,
        );
 
        $vBox = new GtkVBox(false, true);
        $vBox->pack_start($mesg);
 
        foreach ( $inputs as $input ) {
            $input->set_max_length(2);
            $table->attach($input, $layout['left'], $layout['right'],
                $layout['top']++, $layout['bottom']++); 
        }
 
        $vBox->pack_start($table);
        $button = new GtkButton("Calculate"); 
        $button->connect("clicked", array($this, "calculate")); 
        $vBox->pack_start($this->chalkboard);
        $vBox->pack_start($button, true, false);
 
        $this->add($vBox);  
    }
 
    public function show() {
        $this->show_all();    // 显示窗体 
    }
 
    private function notice($mesg) {
        $this->chalkboard->set_text($mesg);
    }
 
    /**
     * 取得用户输入方法
     */
    public function calculate() {
        $operants = array();
        $inputs   = $this->inputs;
        foreach ($inputs as $input) {
            $number = $input->get_text();
            if (!preg_match('/^\s*\d+\s*$/', $number)) {
                $this->notice('pleas input for integer(0-99)');
                return ;
            }
            array_push($operants, $number);
        }
        $length = count($operants);
        try {
            $this->search($operants, 4);
        } catch (Exception $e) {
            $this->notice($e->getMessage());
            return;
        }
        $this->notice('can\'t compute!');
        return;
    }
 
    /**
     * 求24点算法PHP实现
     */
    private function search($expressions, $level) {
        if ($level == 1) {
            $result = 'return ' . $expressions[0] . ';';
            if ( abs(eval($result) - $this->needle) <= $this->precision) {
                throw new Exception($expressions[0]);
            }
        }
        for ($i=0;$i<$level;$i++) {
            for ($j=$i+1;$j<$level;$j++) {
                $expLeft  = $expressions[$i];
                $expRight = $expressions[$j];
                $expressions[$j] = $expressions[$level - 1];
 
                $expressions[$i] = '(' . $expLeft . ' + ' . $expRight . ')';
                $this->search($expressions, $level - 1);
 
                $expressions[$i] = '(' . $expLeft . ' * ' . $expRight . ')';
                $this->search($expressions, $level - 1);
 
                $expressions[$i] = '(' . $expLeft . ' - ' . $expRight . ')';
                $this->search($expressions, $level - 1);
 
                $expressions[$i] = '(' . $expRight . ' - ' . $expLeft . ')';
                $this->search($expressions, $level - 1);
                
                if ($expLeft != 0) {
                    $expressions[$i] = '(' . $expRight . ' / ' . $expLeft . ')';
                    $this->search($expressions, $level - 1);
                }
                
                if ($expRight != 0) {
                    $expressions[$i] = '(' . $expLeft . ' / ' . $expRight . ')';
                    $this->search($expressions, $level - 1);
                }
                $expressions[$i] = $expLeft;
                $expressions[$j] = $expRight;
            }
        }
        return false;
    }
 
    function __destruct() {
        Gtk::main_quit();
    }
}
 
new TwentyFourCal();
Gtk::main();    //进入GTK主循环 
?>
