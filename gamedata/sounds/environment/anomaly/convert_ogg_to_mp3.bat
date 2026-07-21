@echo off
set FF=D:\Apps\ffmpeg-master-latest-win64-gpl-shared\bin\ffmpeg.exe

for %%F in (*.ogg) do (
    "%FF%" -y -i "%%F" -acodec libmp3lame -b:a 96k "%%~nF.mp3"
)

echo Done
pause
