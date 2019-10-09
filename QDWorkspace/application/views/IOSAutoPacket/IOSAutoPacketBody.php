<body>
    <div id="base_bg">
        <div id="config_bg">
            <div>
                <span>init_time<?php echo $init_config_time ?></span><br>
                <span>get_branch_time<?php echo $get_branch_time ?></span><br>
                <span>cur_branch_time<?php echo $get_cur_branch_time ?></span>
            </div>
            <div>
                <h1 style="display: inline">IOS打包</h1>
                <img class="help" id="help_view" src="<?php echo base_url();?>resource/help.png<?php echo '?' . date("YmdHis")?>">
            </div>

            <form id="form" action="">
                <div class="property_div">
                    <span class="title">当前分支:&nbsp</span>
                        <?php echo "<input type=\"text\" id=\"select_branch\" name=\"select_branch\" value='$cur_branch' list='branch'>"?>
                    <datalist id="branch">
                        <?php foreach ($branch_list_result as $value): ?>
                                <?php echo "<option value='$value'>$value</option>" . PHP_EOL; ?>
                        <?php endforeach;?>
                    </datalist>
                </div>

                <div class="property_div">
                    <span class="title">目标名:&nbsp</span><select name="target" id="target">
                        <?php echo "<option value='$test_target_key' selected=\"selected\">$test_target_key</option>" . PHP_EOL; ?>
                        <?php echo "<option value='$build_target_key'>$build_target_key</option>"; ?>
                    </select>
                </div>

                <div class="property_div">
                    <span class="title">Version:&nbsp</span>
                    <?php echo "<input type=\"text\" id=\"version\" name=\"version\" value='$version_dic[$test_target_key]'>"?>
                </div>

                <div class="property_div">
                    <span class="title">Build:&nbsp</span>
                    <?php echo "<input type=\"text\" id=\"build\" name=\"build\" value='$build_dic[$test_target_key]'>"?>
                </div>

                <div class="property_div">
                    <span class="title">IPA名后缀:&nbsp</span><input type="text" name="ext">
                </div>

                <div class="property_div">
                    <span class="title">项目组:&nbsp</span><select name="group" id="group">
                        <?php foreach ($group_value as $name => $value): ?>
                            <?php echo "<option value='$value'>$name</option>" . PHP_EOL ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="property_div">
                    <span class="title">ipa包文件夹名:&nbsp</span><input type="text" name="dir">
                </div>

                <div class="property_div">
                    <span class="title">是否灰度:&nbsp</span><select name="is_gray" id="is_gray">
                        <option value="0">不是</option>
                        <option value="1">是</option>
                    </select>
                </div>
                <div class="property_div">
                    <span class="title">配置:&nbsp</span><select name="configure" id="configure">
                        <option value="Release">Release</option>
                        <option value="Debug">Debug</option>
                    </select>
                </div>
                
                <button type="button" id="submit">Packet</button>
            </form>
        </div>
        <div id="result_bg">
            <div id="queue">
                <span class="ptitle">当前队列</span>
                <div id="queue_content">
                </div>
            </div>
            <div id="history">
                <span class="ptitle">历史记录</span>
                <div id="history_content"></div>
            </div>
        </div>
    </div>
</body>