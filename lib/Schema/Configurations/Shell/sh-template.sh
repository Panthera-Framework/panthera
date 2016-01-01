#!/bin/{$SH$} --init-file

######################################################
# Panthera Framework 2 shell configuration file
#
# @author Damian Kęska <damian@pantheraframework.org>
######################################################

export PF2_PATH="{$FRAMEWORK_PATH$}"
export APP_PATH="{$APP_PATH$}"
export PATH="$PATH:{$FRAMEWORK_PATH$}/Binaries/:{$APP_PATH$}/.content/Binaries/:{$VENDOR_PATH$}/bin"
export PS1="[\$(tput setaf 3)\u\$(tput sgr0)|{$PROJECT_NAME$}|\$(tput setaf 2)\W\$(tput sgr0)]\$ "

# execute custom scripts at startup
if [ -e "{$APP_PATH$}/.content/Schema/Configurations/Shell/sh-startup.sh" ]
then
    source {$APP_PATH$}/.content/Schema/Configurations/Shell/sh-startup.sh
fi

# aliases
reload()
{
    if [ "$1" == "--help" ]
    then
        echo "Rebuilds configuration and re-run shell again"
    else
        deploy Build/Environment/ShellConfiguration
        source `whereis shell | cut -d':' -f2`
    fi
}

# psysh/php_shell
psysh()
{
    goto_app
    psyCMD="{$VENDOR_PATH$}/bin/psysh {$PSYSH_BOOTSTRAP$} $@"
    eval $psyCMD
}

goto_app()
{
    cd "$APP_PATH"
}

goto_fw()
{
    cd "$PF2_PATH"
}

commands()
{
    echo "$(tput setaf 2)Your application commands:$(tput setaf 3)"
    ls {$APP_PATH$}/.content/Binaries/

    echo ""
    echo "$(tput setaf 2)Panthera Framework 2 builtin commands:$(tput setaf 3)"
    ls {$FRAMEWORK_PATH$}/Binaries/
    echo "reload goto_app goto_fw welcome commands psysh"
    if [ -d {$VENDOR_PATH$}/bin ];
    then
        ls {$VENDOR_PATH$}/bin
    fi
    echo "$(tput sgr0)"
}

welcome()
{
    clear
    echo "$(tput setaf 2)Welcome to $(tput setaf 3){$PROJECT_NAME$} $(tput setaf 2)shell"
    echo "Your project is localized at path: $(tput setaf 3)$APP_PATH$(tput sgr0)"
    echo ""
    commands
    echo ""

    echo "$(tput setaf 2)Type \"$(tput setaf 1)commands$(tput setaf 2)\" to see list of available commands again any time$(tput sgr0)"
}

phinx()
{
    echo "Better please use command \"migrations\" for advanced operations on database"
    echo "To securely migrate your database please use \"deploy Build/Database/Migrate\" as this command will include all Panthera Framework 2 and your application logic"
    echo "To anyway use raw phinx you could still execute: \"{$VENDOR_PATH$}/bin/phinx\""
}
welcome