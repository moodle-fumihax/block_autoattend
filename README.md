# block_autoattend

## Overview
 This autoattend block is modification of the Attendance block by Mr. Dmitry Pupinin et. al.
 In addition to the original manual mode, automatic attendance mode (from the access log of Moodle) and semi-automatic 
attendance mode (user clicks a link) are also possible.

 **I strongly recommend a combination of autoattendance block (autoattend) and autoattendance module (autoattendmod).**

## Functions
 This is the block for taking attendance. 
 The taking of attendance, there are three modes of manual, automatic and semi-automatic.

### Automatic mode
  Attendance is taken automatically when the student has access to the course.
  - As the access log is checked by cron, when you want to get the newest information, time lag will occur.
  - It is also possible to obtain the newest information by clicking the "Refresh" button manually.
  - You can add a restriction by IP address.
  - You can also prohibit the attendance from the same PC.
  - If log of Moodle is left, you can retry to attend at any time.

### Semi-automatic mode
  You take attendance by the student clicks the link of attendance.
  - It is required that the student clicks the link of attendance.
  - You can confirm the attendance in real time.
  - You can add a restriction by IP address and/or key words.
  - You can also prohibit the attendance from the same PC.
  - If you must have installed the attendance module (autoattendmod), it will be in attendance link instead.
  - If you have not installed the attendance module, it is necessary to put paste the following 
    link as an attendee link.
         MOODLE_URL/blocks/autoattend/semiautobutton.php?course=[course id]
  - If there is no class of semi-automatic mode is taking attendance in the appropriate time, attendance link 
    displays the detailed data of attendance.

### Manual mode
  Teachers will record by taking the roll call manually. It is flexibil, but cumbersome.
  - Please use in the classroom do not use the computer or use in the correction after taking the automatic and semi-automatic mode

----------
## In Japanese

### 概要
　このブロックとモジュールは Dmitry Pupinin 氏の Attendance block/module を改造
したものです．元々の手動で出席をとるモードに加えて，自動・半自動モードが追加
されています．また手動モードとも組み合わせて，かなり柔軟に出席を取る事が可能です．

### 機能
　出席を取るためのブロックとモジュールです．出席の取り方は，手動，自動，半自動の
3種類があります．それぞれ利点と欠点がありますので，用途・環境によって使い分けて
ください．

#### 自動：ユーザがコースにアクセスした場合に自動で出席が取られます．
　cronなどによりアクセスログをみて出席の確認を取るため，最新の情報を得たい
場合などにはタイムラグが生じます．ただし，手動でリフレッシュボタンをクリックすれば最新の情報を得ることも可能です．IPアドレスによる制限を追加できます．ログさえ残っていれば，いつでも出席を取り直せます．

#### 半自動：ユーザが出欠モジュールをクリックすることにより出欠を取ります．
　学生が意識して出欠リンクをクリックする必要があります．
リアルタイムで出席を確認できます．IPアドレス，キーワードによる制限を追加できます．同一のマシンからの出席を禁止することもできます．

#### 手動：教師が手動で点呼を取って記録します．
　融通が利きますが面倒です．コンピュータを使わない授業や自動・半自動で取った後の修正で使用すると便利でしょう．

### モジュールとの併用
　ブロックのみでも作動しますが，モジュールを追加すると，以下の機能が追加されます．
+ 出席点が評定に加算される．
+ 自動モードの場合に cronで最新の状態に更新できる．
+ 半自動モードで出席エントリのリンクとして使用できる
