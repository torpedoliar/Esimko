package com.esimko.mobile.ui.common

import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.AnnotatedString
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.OffsetMapping
import androidx.compose.ui.text.input.TransformedText
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.util.MoneyFormatter

@Composable
fun AmountField(
    value: String,
    onValueChange: (String) -> Unit,
    modifier: Modifier = Modifier,
    label: String = "Nominal",
    supportingText: String? = null,
    isError: Boolean = false,
    enabled: Boolean = true,
    imeAction: ImeAction = ImeAction.Done,
    onImeAction: (() -> Unit)? = null
) {
    OutlinedTextField(
        value = value,
        onValueChange = { onValueChange(MoneyFormatter.digitsToLong(it).toString()) },
        modifier = modifier.fillMaxWidth(),
        label = { Text(label) },
        prefix = { Text("Rp") },
        supportingText = supportingText?.let { { Text(it) } },
        isError = isError,
        enabled = enabled,
        singleLine = true,
        textStyle = MoneyRow,
        keyboardOptions = KeyboardOptions(
            keyboardType = KeyboardType.NumberPassword,
            imeAction = imeAction
        ),
        keyboardActions = KeyboardActions(onDone = { onImeAction?.invoke() }),
        visualTransformation = ThousandsTransformation()
    )
}

private class ThousandsTransformation : VisualTransformation {
    override fun filter(text: AnnotatedString): TransformedText {
        val digits = text.text.filter { it.isDigit() }
        val formatted = if (digits.isEmpty()) "" else MoneyFormatter.plain(digits.toLongOrNull() ?: 0L)
        val offsets = object : OffsetMapping {
            private fun dotsBefore(digitIndex: Int): Int {
                val remaining = digits.length - digitIndex
                return if (digits.length <= 3 || remaining <= 0) 0
                else (digits.length - 1) / 3 - (remaining - 1) / 3
            }
            override fun originalToTransformed(offset: Int): Int {
                val clamped = offset.coerceIn(0, digits.length)
                return clamped + dotsBefore(clamped)
            }
            override fun transformedToOriginal(offset: Int): Int {
                val clamped = offset.coerceIn(0, formatted.length)
                return formatted.take(clamped).count { it.isDigit() }
            }
        }
        return TransformedText(AnnotatedString(formatted), offsets)
    }
}

@LightDarkPreview
@Composable
private fun AmountFieldPreview() {
    EsimkoPreview {
        Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
            AmountField(value = "", onValueChange = {})
            AmountField(value = "1250000", onValueChange = {})
            AmountField(
                value = "5000",
                onValueChange = {},
                isError = true,
                supportingText = "Minimal Rp 10.000"
            )
        }
    }
}
