<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 8/17/18
 * Time: 6:30 PM
 */

class APILoggingConfigurator implements LoggerConfigurator
{

    /**
     * Configures log4php based on the given configuration.
     *
     * All configurators implementations must implement this interface.
     *
     * @param LoggerHierarchy $hierarchy The hierarchy on which to perform
     *        the configuration.
     * @param mixed $input Either path to the config file or the
     *        configuration as an array.
     */
    public function configure(LoggerHierarchy $hierarchy, $input = null)
    {
        $appFile = new LoggerAppenderFile('foo');
        $appFile->setFile('D:/Temp/log.txt');
        $appFile->setAppend(true);
        $appFile->setThreshold('all');
        $appFile->activateOptions();

        // Use a different layout for the next appender
        $layout = new LoggerLayoutPattern();
        $layout->setConversionPattern("%date %logger %msg%newline");
        $layout->activateOptions();

        // Create an appender which echoes log events, using a custom layout
        // and with the threshold set to INFO
        $appEcho = new LoggerAppenderEcho('bar');
        $appEcho->setLayout($layout);
        $appEcho->setThreshold('info');
        $appEcho->activateOptions();

        // Add both appenders to the root logger
        $root = $hierarchy->getRootLogger();
        $root->addAppender($appFile);
        $root->addAppender($appEcho);
    }
}